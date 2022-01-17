<?php

// AUTO INSTANCE OBJECT READ
if (empty($Read)):
    $Read = new Read;
endif;
?>

<header class="dashboard_header">
    <div class="dashboard_header_title">
        <h1 class="icon-cart">Pedidos</h1>
        <p class="dashboard_header_breadcrumbs">
            &raquo; <a style="font-weight:normal" href="campus.php?wc=cursos/cursos" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a>
            <span class="crumb">/</span>
            <a style="font-weight:normal" title="Meus Pedidos" href="campus.php?wc=orders/home">Meus Pedidos</a>
            <span class="crumb">/</span>
            <span style="font-weight:600">Transações</span>
        </p>
    </div>
</header>
<div class="dashboard_content">
    <section class="box_content">
        <?php
        $getPage = (filter_input(INPUT_GET, 'page'));
        $Page = ($getPage ? $getPage : 1);
        $Pager = new Pager("campus.php?wc=orders/home&page=", "<", ">", 3);
        $Pager->ExePager($Page, 15);
        $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :user ORDER BY order_confirmation_purchase_date DESC, order_purchase_date DESC LIMIT :limit OFFSET :offset", "user={$User['user_id']}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
//        $Read->ExeRead(DB_EAD_ORDERS, "WHERE user_id = :uid ORDER BY order_purchase_date DESC LIMIT :limit OFFSET :offset", "uid={$User['user_id']}&limit={$Pager->getLimit()}&offset={$Pager->getOffset()}");
        if (!$Read->getResult()):
            $Pager->ReturnPage();
            echo "<div class='trigger trigger_info trigger-none icon-info al_center'>Olá {$User['user_name']}, você ainda não tem pedidos!</div>";
        else:
            echo "<div class='student_gerent_orders_detail'>
                <div class='student_gerent_orders_detail_content'>
                    <div class='j_order_detail'></div>
                    <p class='close'><span class='icon icon-cross icon-notext btn btn_red order_close j_student_order_close student_gerent_orders_detail_content_close'></span></p>
                </div></div>";

            foreach ($Read->getResult() as $StudentOrders):
                $StudentOrders['order_currency'] = ($StudentOrders['order_currency'] ? $StudentOrders['order_currency'] : "BRL");

                $Read->FullRead("SELECT course_title FROM " . DB_EAD_COURSES . " WHERE course_id = :course", "course={$StudentOrders['course_id']}");
                $CourseTitle = ($Read->getResult() ? "Curso {$Read->getResult()[0]['course_title']}" : "Produto #{$StudentOrders['order_product_id']} na Hotmart");
                ?>
                <article class="wc_ead_orders_order">
                <p class="row icon-cart">Número do Pedido
                    <span><?= $StudentOrders['order_transaction']; ?></span>
                </p>

                <p class="row icon-film">Nome do Curso
                    <span><?= $CourseTitle; ?></span>
                </p>

                <p class="row icon-calendar">Data da Compra
                    <span><?= date("d/m/Y \à\s H\hi", strtotime($StudentOrders['order_purchase_date'])); ?></span>
                </p>

                <p class="row icon-coin-dollar">Valor da Compra
                    <span><b>R$ <?= number_format($StudentOrders['order_price'], '2', ',', '.'); ?> (<?= $StudentOrders['order_currency']; ?>) <img width="25" src="<?= BASE; ?>/_cdn/bootcss/images/pay_<?= $StudentOrders['order_payment_type']; ?>.png" alt="<?= $StudentOrders['order_payment_type']; ?>" title="<?= $StudentOrders['order_payment_type']; ?>"/></b></span>
                </p>

                <p class="row icon-connection">Status do Pedido
                    <span class="detail bar_<?= getWcHotmartStatusClass($StudentOrders['order_status']); ?>"><?= getWcHotmartStatus($StudentOrders['order_status']); ?></span>
                </p>
                </article><?php
            endforeach;

            $Pager->ExePaginator(DB_EAD_ORDERS, "WHERE user_id = :uid", "uid={$User['user_id']}");
            echo $Pager->getPaginator();
            echo "<div class='clear'></div>";
        endif;
        ?>
    </section>
</div>