#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <USB.h>
#include <usbhub.h>
#include <hidboot.h>
#include <SPI.h>

USB Usb;
USBHub Hub(&Usb);
HIDBoot<USB_HID_PROTOCOL_KEYBOARD> Keyboard(&Usb);

const char* ssid = "trio";
const char* password = "123456789";

// Your LCD display settings
LiquidCrystal_I2C lcd(0x27, 20, 4); // Initialize LCD with address 0x27 and size 20x4

#define up 14
#define down 4
#define clearButton 12
#define user1Button 26
#define user2Button 27

int currentLine = 0;
String nama_barang[10];
String jumlah_barang[10];
bool lineVisible[10]; // Indicator whether a line is visible or not
int totalLines = 10;

bool usbConnected = false;
String scannedData = "";
unsigned long lastScanTime = 0;
const unsigned long SCAN_TIMEOUT = 1000;

bool dataScanned = false; // Flag to indicate if data has been scanned
int currentUser = 1; // Default to user 1

class KbdRptParser : public KeyboardReportParser {
protected:
  void OnKeyDown(uint8_t mod, uint8_t key);
  void OnKeyPressed(uint8_t key);
  void PrintKey(uint8_t mod, uint8_t key);
};

void KbdRptParser::OnKeyDown(uint8_t mod, uint8_t key) {
  if (usbConnected) {
    PrintKey(mod, key);
  }
}

void KbdRptParser::OnKeyPressed(uint8_t key) {}

void KbdRptParser::PrintKey(uint8_t mod, uint8_t key) {
  uint8_t c = OemToAscii(mod, key);
  if (c) {
    scannedData += (char)c;
  } else {
    scannedData += String(key);
  }
  lastScanTime = millis();
  delay(0);
}

KbdRptParser Prs;

void setup() {
  Serial.begin(115200);
#if !defined(__MIPSEL__)
  while (!Serial);
#endif

  if (Usb.Init() == -1) {
    Serial.println(F("\r\nOSC did not start"));
    while (1);
  }

  delay(200);
  Serial.println("Waiting for USB device...");

  Keyboard.SetReportParser(0, &Prs);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");

  lcd.init();  // Initialize LCD
  lcd.backlight();
  lcd.setCursor(3, 0);
  lcd.print("Selamat Datang");
  lcd.setCursor(3, 1);
  lcd.print("di Daffa Mart");
  delay(2000);
  lcd.clear();

  pinMode(up, INPUT_PULLUP);
  pinMode(down, INPUT_PULLUP);
  pinMode(clearButton, INPUT_PULLUP);
  pinMode(resetButton, INPUT_PULLUP);
  pinMode(user1Button, INPUT_PULLUP);
  pinMode(user2Button, INPUT_PULLUP);

  fetchDataUser1(); // Fetch initial data for user 1
}

void loop() {
  Usb.Task();

  if (!usbConnected && Usb.getUsbTaskState() == USB_STATE_RUNNING) {
    Serial.println("USB device connected");
    usbConnected = true;
  }

  if (usbConnected && !scannedData.isEmpty() && (millis() - lastScanTime > SCAN_TIMEOUT)) {
    Serial.println(scannedData);
    // Process scanned data to LCD
    addScannedData(scannedData); // Add scanned data to array
    sendScannedData(scannedData); // Send scanned data to server
    scannedData = "";
    dataScanned = true; // Data has been scanned
  }

  if (dataScanned) {
    displayLines(); // Display lines after data has been scanned
    dataScanned = false; // Reset flag after displaying
  }

  if (digitalRead(down) == LOW) { 
    scrollDown();
    delay(200);
  }

  if (digitalRead(up) == LOW) {
    scrollUp();
    delay(200);
  }

  if (digitalRead(clearButton) == LOW) {
    clearLine(currentLine % 4);  // Clear the line pointed by currentLine modulo 4
    lineVisible[currentLine % 4] = false; // Mark the line as not visible
    deleteData(); // Send delete request based on current user
    Serial.println("Line Cleared");
    delay(200);
  }

  if (digitalRead(resetButton) == LOW) {
    resetLCD();  // Reset all lines on the LCD
    Serial.println("LCD Reset");
    delay(200);
  }

  if (digitalRead(user1Button) == LOW) {
    currentUser = 1;
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("User 1 selected");
    Serial.println("User 1 selected");
    fetchDataUser1(); // Fetch data for user 1
    delay(1000);
  }

  if (digitalRead(user2Button) == LOW) {
    currentUser = 2;
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("User 2 selected");
    Serial.println("User 2 selected");
    fetchDataUser2(); // Fetch data for user 2
    delay(1000);
  }

  delay(100);
}

void fetchDataUser1() {
  if (WiFi.status() == WL_CONNECTED) { // Check Wi-Fi connection status
    HTTPClient http;
    http.begin("http://8.215.46.96/iot_kasir/get_belanjaan_user1.php"); // Specify the URL for user 1
    int httpCode = http.GET(); // Make the request

    if (httpCode > 0) { // Check for the returning code
      String payload = http.getString();
      Serial.println(payload);
      parseData(payload);
    } else {
      Serial.println("Error on HTTP request");
    }

    http.end(); // Free the resources
  }
}

void fetchDataUser2() {
  if (WiFi.status() == WL_CONNECTED) { // Check Wi-Fi connection status
    HTTPClient http;
    http.begin("http://8.215.46.96/iot_kasir/get_belanjaan_user2.php"); // Specify the URL for user 2
    int httpCode = http.GET(); // Make the request

    if (httpCode > 0) { // Check for the returning code
      String payload = http.getString();
      Serial.println(payload);
      parseData(payload);
    } else {
      Serial.println("Error on HTTP request");
    }

    http.end(); // Free the resources
  }
}

void parseData(String payload) {
  DynamicJsonDocument doc(2048);
  deserializeJson(doc, payload);

  JsonArray array = doc.as<JsonArray>();

  totalLines = array.size();
  for (int i = 0; i < totalLines; i++) {
    nama_barang[i] = array[i]["nama_barang"].as<String>();
    jumlah_barang[i] = array[i]["jumlah_barang"].as<String>();
    lineVisible[i] = true;
  }
}

void displayLines() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Barang     Jumlah");
  
  for (int i = 0; i < 3; i++) { // Change to 3 because the first row is for headers
    if (currentLine + i < totalLines && lineVisible[currentLine + i]) { // Check if the line is still visible before printing
      lcd.setCursor(0, i + 1);
      lcd.print(nama_barang[currentLine + i]);
      lcd.setCursor(14, i + 1);
      lcd.print(jumlah_barang[currentLine + i]);
    }
  }
  // Display indicator at the start or end of the message list
  if (currentLine == 0) {
    lcd.setCursor(19, 0);
    lcd.write(byte(0)); // Indicator at the start position (e.g., vertical line at the right edge)
  } else if (currentLine >= totalLines - 3) { // Adjust to -3 because the first row is for headers
    lcd.setCursor(19, 3);
    lcd.write(byte(1)); // Indicator at the end position (e.g., vertical line at the right edge)
  }
}

void scrollDown() {
  if (currentLine < totalLines - 1) { // Scroll one line at a time
    currentLine++;
    displayLines();
    Serial.println("Scrolled Down");
  } else {
    Serial.println("Reached Bottom");
  }
}

void scrollUp() {
  if (currentLine > 0) { // Scroll one line at a time
    currentLine--;
    displayLines();
    Serial.println("Scrolled Up");
  } else {
    Serial.println("Reached Top");
  }
}

void clearLine(int line) {
  lcd.setCursor(0, line + 1); // Adjust line index because the first row is for headers
  lcd.print("                    ");  // Clear the content of the line
}

void resetLCD() {
  lcd.clear();
  currentLine = 0;
  for (int i = 0; i < 10; i++) {
    lineVisible[i] = true; // Mark all lines as visible again
  }
  displayLines();
}

void sendScannedData(String data) {
  data.trim();  // Remove any leading or trailing whitespace, including \r and \n

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url;
    if (currentUser == 1) {
      url = "http://8.215.46.96/iot_kasir/kirimdata.php?kode_barang=" + urlEncode(data);
    } else if (currentUser == 2) {
      url = "http://8.215.46.96/iot_kasir/kirimdata2.php?kode_barang=" + urlEncode(data);
    }
    
    Serial.println("Request URL: " + url); // Print the URL to Serial Monitor
    http.begin(url);
    int httpResponseCode = http.GET();
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Data sent successfully");
      Serial.println("Response: " + response); // Print the server response
      // Update LCD data after sending
      if (currentUser == 1) {
        fetchDataUser1();
      } else if (currentUser == 2) {
        fetchDataUser2();
      }
    } else {
      Serial.println("Error in sending data");
      Serial.println(http.errorToString(httpResponseCode));
    }
    http.end();
  } else {
    Serial.println("WiFi not connected");
  }
}


void deleteData() {
  if (WiFi.status() == WL_CONNECTED) { // Check Wi-Fi connection status
    HTTPClient http;
    String url = currentUser == 1 ? "http://8.215.46.96/iot_kasir/get_belanjaan_user1.php?nama_barang=" + urlEncode(nama_barang[currentLine % 4]) :
                                    "http://8.215.46.96/iot_kasir/get_belanjaan_user2.php?nama_barang=" + urlEncode(nama_barang[currentLine % 4]);
    Serial.println("Request URL: " + url); // Print the URL to Serial Monitor
    http.begin(url);
    int httpCode = http.GET(); // Make the request

    if (httpCode > 0) { // Check for the returning code
      String payload = http.getString();
      Serial.println(payload);
      fetchDataUser2();
      fetchDataUser1(); // Fetch and update the LCD with new data
    } else {
      Serial.println("Error on HTTP request");
    }

    http.end(); // Free the resources
  }
}

String urlEncode(String str) {
  String encodedString = "";
  char c;
  char code0;
  char code1;
  char code2;
  for (int i = 0; i < str.length(); i++) {
    c = str.charAt(i);
    if (c == ' ') {
      encodedString += '+';
    } else if (isalnum(c)) {
      encodedString += c;
    } else {
      code1 = (c & 0xf) + '0';
      if ((c & 0xf) > 9) {
        code1 = (c & 0xf) - 10 + 'A';
      }
      c = (c >> 4) & 0xf;
      code0 = c + '0';
      if (c > 9) {
        code0 = c - 10 + 'A';
      }
      code2 = '\0';
      encodedString += '%';
      encodedString += code0;
      encodedString += code1;
    }
    yield();
  }
  return encodedString;
}

void addScannedData(String scannedData) {
  // Find the first empty index or next available index
  int index = 0;
  while (index < totalLines && lineVisible[index]) {
    index++;
  }
  
  // Add new data to the array
  if (index < totalLines) {
    nama_barang[index] = scannedData;
    jumlah_barang[index] = "1"; // Assuming default quantity is 1
    lineVisible[index] = true; // Mark the line as visible
  } else {
    Serial.println("No available lines to display scanned data.");
  }
}
