<div class="col-xs-12 col-sm-6 col-md-3">
    <div class="destaque">
        <div class="img-wrap">
            <a href="<?= BASE; ?>/curso/<?= $course_name; ?>" title="<?= $course_title; ?>">
                <img src="<?= BASE; ?>/tim.php?src=uploads/<?= $course_cover; ?>&w=400&h=250" alt="<?= $course_title; ?>"/>
            </a>
        </div>
        <div class="destaque-content">
            <h3><a href="<?= BASE; ?>/curso/<?= $course_name; ?>" title="<?= $course_title; ?>"><?= $course_title; ?></a></h3>
            <?php if ($course_vendor_price > '0.00'): ?>
                <span class="price"><strong>R$ <?php echo number_format($course_vendor_price, 2, ',', '.'); ?></strong></span>
            <?php else: ?>
                <span class="price"><strong>Grátis</strong></span>
            <?php endif; ?>

            <p><?= $course_headline; ?></p>

            <div class="counter">
                <i class="fa fa-users"></i>
                <span><?= $StudentCount; ?></span>
            </div>

            <div class="counter">
                <span><?= $Rank; ?> (<?= $Aval; ?>)</span>
            </div>

            <div class="buy-wrap">
                <a href="#" class="btn btn-green j_view_course" data-toggle="modal" data-target="#modal-curso-<?= $course_name; ?>" data-course-id="<?= $course_id; ?>">
                    <i class="fa fa-eye"></i>
                    Pré-visualizar
                </a>
                <?php if ($course_vendor_price > '0.00'): ?>
                    <div class="discount">
                        <small>R$ <?php echo number_format($course_vendor_price, 2, ',', '.'); ?> a vista ou em até <br><strong><?= $NumSplit; ?>x de R$ <?= $SplitPrice; ?> sem juros no cartão</strong></small>
                    </div>
                <?php else: ?>
                    <div class="discount">
                        <small>Curso 100% gratuito! <br><strong>Comece estudar hoje mesmo!</strong></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>