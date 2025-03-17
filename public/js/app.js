// Configure Axios defaults
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add CSRF Token to all requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Function to refresh payments data
function refreshPayments() {
    axios.get('/admin/payments')
        .then(response => {
            // Update the payments table with new data
            const paymentsTable = document.getElementById('payments-table-body');
            paymentsTable.innerHTML = ''; // Clear existing data

            response.data.forEach(payment => {
                const row = `<tr>
                    <td>${payment.id}</td>
                    <td>${payment.amount}</td>
                    <td>${payment.payment_method}</td>
                    <td>${payment.status}</td>
                    <td>${payment.created_at}</td>
                    <td>
                        <button onclick="editPayment(${payment.id})">Edit</button>
                        <button onclick="deletePayment(${payment.id})">Delete</button>
                    </td>
                </tr>`;
                paymentsTable.innerHTML += row; // Append new data
            });
        })
        .catch(error => {
            console.error('Error fetching payments:', error);
        });
}

// Call refreshPayments periodically or on specific events
setInterval(refreshPayments, 5000); // Refresh every 5 seconds

// Function to create a new payment
function createPayment() {
    const formData = new FormData(document.getElementById('payment-form'));
    axios.post('/admin/payments', formData)
        .then(response => {
            refreshPayments(); // Refresh the payments list
            alert('Payment created successfully.');
        })
        .catch(error => {
            console.error('Error creating payment:', error);
        });
}

// Function to update a payment
function editPayment(paymentId) {
    // Logic to open edit modal and update payment
}

// Function to delete a payment
function deletePayment(paymentId) {
    axios.delete(`/admin/payments/${paymentId}`)
        .then(response => {
            refreshPayments(); // Refresh the payments list
            alert('Payment deleted successfully.');
        })
        .catch(error => {
            console.error('Error deleting payment:', error);
        });
}
