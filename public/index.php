<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use RemoteMerge\Esewa\EsewaFactory;

session_start();

$epay = null;
$paymentForm = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    try {
        $epay = EsewaFactory::createEpay([
            'environment' => 'test',
            'product_code' => 'EPAYTEST',
            'secret_key' => '8gBm/:&EnhH.1/q',
            'success_url' => 'http://localhost:8000/success.php',
            'failure_url' => 'http://localhost:8000/failure.php'
        ]);

        $productData = [
            1 => ['name' => 'Premium Laptop', 'price' => 85000],
            2 => ['name' => 'Wireless Headphones', 'price' => 12500],
            3 => ['name' => 'Smart Watch', 'price' => 25000],
            4 => ['name' => 'Gaming Mouse', 'price' => 3500],
            5 => ['name' => 'Mechanical Keyboard', 'price' => 8500],
            6 => ['name' => 'Monitor 4K', 'price' => 45000]
        ];

        $productId = (int)$_POST['product_id'];
        if (!isset($productData[$productId])) {
            throw new Exception('Invalid product selected');
        }

        $product = $productData[$productId];
        $transactionUuid = Uuid::uuid4()->toString();

        $paymentData = [
            'amount' => $product['price'],
            'tax_amount' => $product['price'] * 0.13,
            'product_service_charge' => 0,
            'product_delivery_charge' => 100,
            'transaction_uuid' => $transactionUuid
        ];

        $paymentForm = $epay->createPayment($paymentData);
        $_SESSION['transaction_uuid'] = $transactionUuid;
        $_SESSION['product_name'] = $product['name'];
        $_SESSION['amount'] = $product['price'];

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Premium Electronics Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.css"
          integrity="sha256-lQQ0StO/37OizAM1JKQP0z6xGFqiITYD/NeXfiyfCA4=" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-clifford: #da373d;
            --color-primary: #1e40af;
            --color-secondary: #7c3aed;
            --color-accent: #f59e0b;
            --color-esewa: #60a85f;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Premium Electronics Store</h1>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Discover the latest technology with seamless eSewa payment
            integration</p>
    </div>
</section>

<?php if ($error): ?>
    <!-- Error Message -->
    <div class="container mx-auto px-4 py-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if ($paymentForm): ?>
    <!-- Payment Form Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg max-w-md w-full mx-4">
            <h3 class="text-2xl font-bold mb-6 text-center">Complete Payment</h3>
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <p class="text-sm text-gray-600">Product: <?= htmlspecialchars($_SESSION['product_name']) ?></p>
                <p class="text-sm text-gray-600">Amount: NPR <?= number_format($_SESSION['amount']) ?></p>
                <p class="text-sm text-gray-600">Tax (13%): NPR <?= number_format($_SESSION['amount'] * 0.13) ?></p>
                <p class="text-sm text-gray-600">Delivery: NPR 100</p>
                <hr class="my-2">
                <p class="font-bold">Total: NPR <?= number_format($paymentForm['total_amount']) ?></p>
            </div>

            <form action="<?= $epay->getFormActionUrl() ?>" method="POST" id="esewaForm">
                <?php foreach ($paymentForm as $key => $value): ?>
                    <input type="hidden" name="<?= htmlspecialchars((string)$key) ?>"
                           value="<?= htmlspecialchars((string)$value) ?>">
                <?php endforeach; ?>

                <div class="flex space-x-4">
                    <button type="button" onclick="closeModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 bg-esewa text-white py-3 px-6 rounded-lg hover:bg-green-600 transition-colors">
                        <i class="fas fa-mobile-alt mr-2"></i>
                        Pay with eSewa
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Products Section -->
<section id="products" class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Featured Products</h2>
            <p class="text-xl text-gray-600">Premium electronics with secure eSewa payment</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <!-- Product 1 -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="h-48 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Premium Laptop"
                        class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Premium Laptop</h3>
                    <p class="text-gray-600 mb-4">High-performance laptop with latest processors and premium build
                        quality.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-primary">NPR 85,000</span>
                        <form method="POST" class="inline">
                            <input type="hidden" name="product_id" value="1">
                            <button type="submit"
                                    class="bg-esewa text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>Buy Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product 2 -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="h-48 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Wireless Headphones"
                        class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Wireless Headphones</h3>
                    <p class="text-gray-600 mb-4">Premium noise-canceling wireless headphones with superior sound
                        quality.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-primary">NPR 12,500</span>
                        <form method="POST" class="inline">
                            <input type="hidden" name="product_id" value="2">
                            <button type="submit"
                                    class="bg-esewa text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>Buy Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product 3 -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="h-48 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Smart Watch"
                        class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Smart Watch</h3>
                    <p class="text-gray-600 mb-4">Advanced smartwatch with health monitoring and fitness tracking
                        features.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-primary">NPR 25,000</span>
                        <form method="POST" class="inline">
                            <input type="hidden" name="product_id" value="3">
                            <button type="submit"
                                    class="bg-esewa text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>Buy Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product 4 -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="h-48 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Gaming Mouse"
                        class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Gaming Mouse</h3>
                    <p class="text-gray-600 mb-4">High-precision gaming mouse with customizable RGB lighting and
                        ergonomic design.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-primary">NPR 3,500</span>
                        <form method="POST" class="inline">
                            <input type="hidden" name="product_id" value="4">
                            <button type="submit"
                                    class="bg-esewa text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>Buy Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product 5 -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="h-48 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1541140532154-b024d705b90a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Mechanical Keyboard"
                        class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Mechanical Keyboard</h3>
                    <p class="text-gray-600 mb-4">Premium mechanical keyboard with tactile switches and customizable
                        backlighting.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-primary">NPR 8,500</span>
                        <form method="POST" class="inline">
                            <input type="hidden" name="product_id" value="5">
                            <button type="submit"
                                    class="bg-esewa text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>Buy Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product 6 -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                <div class="h-48 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="4K Monitor"
                        class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Monitor 4K</h3>
                    <p class="text-gray-600 mb-4">Ultra-high-definition 4K monitor with vibrant colors and wide viewing
                        angles.</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-primary">NPR 45,000</span>
                        <form method="POST" class="inline">
                            <input type="hidden" name="product_id" value="6">
                            <button type="submit"
                                    class="bg-esewa text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>Buy Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Features Section -->
<section class="bg-white py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose TechStore?</h2>
            <p class="text-xl text-gray-600">Premium products with secure payment solutions</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-esewa text-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Secure Payments</h3>
                <p class="text-gray-600">Safe and secure transactions with eSewa integration</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Fast Delivery</h3>
                <p class="text-gray-600">Quick and reliable delivery across Nepal</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-accent text-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Premium Quality</h3>
                <p class="text-gray-600">Only the best products from trusted brands</p>
            </div>
        </div>
    </div>
</section>

<script>
    function closeModal() {
        const modal = document.getElementById('paymentModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Auto-submit payment form after user confirms
    <?php if ($paymentForm): ?>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('paymentModal');
        modal.style.display = 'flex';
    });
    <?php endif; ?>

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>

</body>
</html>
