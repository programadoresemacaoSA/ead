<!-- Footer -->
<footer class="footer">
    <div class="container section-container">
        <div class="footer-content">
            <div class="footer-menu">
                <div class="menu-column">
                    <h3 class="lightwhite">O <?= SITE_NAME; ?></h3>
                    <ul>
                        <li><a href="<?= BASE; ?>/explorar/cursos">Nossos cursos</a></li>
                        <li><a href="<?= BASE; ?>/certificados">Consultar certificados</a></li>
                        <li><a href="<?= BASE; ?>/blog">Nosso blog</a></li>
                        <li><a href="<?= BASE; ?>/campus">Área do aluno</a></li>
                    </ul>
                </div>
                <div class="menu-column">
                    <h3 class="lightwhite">Últimas do Blog</h3>
                    <ul>
                        <?php
                        $Read->ExeRead(DB_POSTS, "WHERE post_status = 1 AND post_date <= NOW() ORDER BY post_date DESC LIMIT 4");
                        if ($Read->getResult()):
                            foreach ($Read->getResult() AS $LastUpdate):
                                $Title = Check::Words($LastUpdate['post_title'], 6);
                                echo "<li><a href='" . BASE . "/artigo/{$LastUpdate['post_name']}' title='{$LastUpdate['post_title']}'>{$Title}</a></li>";
                            endforeach;
                        endif;
                        ?>
                    </ul>
                </div>
            </div>
            <div class="footer-counters">
                <div class="logo">
                    <div class="img-wrap">
                        <img src="<?= INCLUDE_PATH; ?>/img/logo.png" alt="<?= SITE_NAME; ?>">
                    </div>
                </div>
                <div class="counters">
                    <div class="counter">
                        <?php
                        $Read->FullRead("SELECT count(enrollment_id) AS TotalEnrollment FROM " . DB_EAD_ENROLLMENTS);
                        $StudentCountTotal = str_pad($Read->getResult()[0]['TotalEnrollment'], 1, 0, 0);
                        ?>
                        <strong><?= $StudentCountTotal; ?></strong>
                        <p>alunos estudando</p>
                    </div>
                    <div class="counter">
                        <?php
                        $Read->FullRead("SELECT COUNT(class_id) AS ClassCount FROM " . DB_EAD_CLASSES);
                        $ClassCountTotal = $Read->getResult()[0]['ClassCount'];
                        ?>
                        <strong><?= $ClassCountTotal; ?></strong>
                        <p>aulas em vídeo</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="site-details">
            <div class="site-links">
                <a href="<?= BASE; ?>/contato">Fale Conosco</a>
                <a href="<?= BASE; ?>/termos">Termos de Uso e Políticas de Privacidade</a>
            </div>
            <div class="copyright">
                <span>&copy; <script>document.write((new Date).getFullYear());</script> <?= SITE_NAME; ?>. Todos os direitos reservados.</span>
            </div>
        </div>
    </div>
</footer>
<!-- // Footer -->