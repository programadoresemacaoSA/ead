<div class="breadcrumb">
    <div class="container">
        <span>Você está em: <strong><?= ($URL['1'] == 'home' ? 'Carrinho' : ($URL['1'] == 'pagamento' ? 'Finalizar compra' : ($URL['1'] == 'obrigado' ? 'Obrigado' : ($URL['1'] == 'login' ? 'Conecte-se para continuar' : SITE_NAME)))); ?></strong></span>
    </div>
</div>
<section class="blog">
    <div class="container">
        <?php require '_cdn/widgets/ecommerce/cart.php'; ?>
    </div>
</section>
<?php require 'inc/newsletter.php'; ?>