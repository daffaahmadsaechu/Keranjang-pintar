const express = require('express');
const app = express();
const cors = require('cors');
const midtransClient = require('midtrans-client');

// Middleware untuk menangani request body
app.use(express.json());
app.use(cors());

// Create Snap API instance
let snap = new midtransClient.Snap({
    isProduction: false,
    serverKey: 'SB-Mid-server-jVWJJNVszRYSgldPwDN6zvNt',
    clientKey: 'SB-Mid-client-Ll_hNUyXQfVw6LFT'
});

app.post('/process-cashless-payment', async (req, res) => {
    try {
        console.log('Received payment request:', req.body);
        // Terima data belanjaan dan total belanja dari frontend
        const { item_details, transaction_details } = req.body;

        // Buat transaksi pembayaran menggunakan Snap API
        const transactionToken = await snap.createTransactionToken({
            item_details: item_details,
            transaction_details: {
                order_id: "order-id-node-" + Math.floor(Math.random() * 1000000),
                gross_amount: transaction_details.gross_amount
            }
        });

        // Kirim respons ke frontend dengan token transaksi
        res.json({ token: transactionToken });
        // Simulate processing payment and generating redirect URL
    const redirect_url = 'https://sandbox.midtrans.com/snap/v2/vtweb/redirect-url'; // Replace with actual Midtrans URL
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});


app.listen(3000, () => {
    console.log('Server is running on port 3000');
});
