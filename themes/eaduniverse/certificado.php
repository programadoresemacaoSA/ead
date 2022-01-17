<?php
$Read->ExeRead(DB_EAD_STUDENT_CERTIFICATES, "WHERE certificate_key = :s", "s={$URL[1]}");
if (!$Read->getResult()):
    header("Location: " . BASE . "/certificados");
else:
    extract($Read->getResult()[0]);

    //PEGA CURSO
    $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :cid", "cid={$course_id}");
    $Course = $Read->getResult() ? $Read->getResult()[0] : null;

    //PEGA ALUNO
    $Read->ExeRead(DB_USERS, "WHERE user_id = :uid", "uid={$user_id}");
    $Student = $Read->getResult() ? $Read->getResult()[0] : null;

    $StudentDocument = strrev(preg_replace('/\d/', '*', strrev($Student['user_document']), 5));

    //ENROLLMENTS
    $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :cid AND user_id = :uid", "cid={$course_id}&uid={$user_id}");
    $Enrollment = $Read->getResult() ? $Read->getResult()[0] : null;
endif;
?>

<div class="app_about_certificates_student_header bg-campus" style="background-color: <?= $Course['course_color']; ?>" >
    <div class="app_about_certificates_student_header_content">
        <img src="<?= BASE; ?>/uploads/<?= $Course['course_cover']; ?>" alt="<?= $Course['course_title']; ?>" title="<?= $Course['course_title']; ?>"/>
    </div>
</div>

<article class="app_about_certificates_student_content">
    <header>
        <span class="icon-checkmark icon-notext" style="color: #8aae4a"></span>
        <h2>Certificamos que <strong><?= $Student['user_name']; ?> <?= $Student['user_lastname']; ?></strong> concluiu o curso online <strong><?= $Course['course_title']; ?></strong> de carga horária estimada em <?= $Course['course_certification_workload']; ?> horas, no período de <?= date("d/m/Y", strtotime($Enrollment['enrollment_start'])); ?> a <?= date("d/m/Y", strtotime($certificate_issued)); ?>.</h2>
    </header>

    <div class="app_blog_post_content_share" style="margin-bottom: 20px;">
        <div class="app_blog_post_content_share_cta">
            <span class="icon-heart font_red"></span>Compartilhe:
        </div>
        <div class="fb-share-button" data-href="<?= BASE; ?>/certificado/<?= $URL[1]; ?>" data-layout="button_count" data-size="large" data-mobile-iframe="true"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= BASE; ?>/certificado/<?= $URL[1]; ?>">Compartilhar</a></div>
        <a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-hashtag-button" data-size="large"  data-text="Certificado de <?= $Student['user_name']; ?> na <?= SITE_NAME; ?>" data-lang="pt" data-show-count="true" data-hashtags="<?= SITE_NAME; ?>" data-url="<?= BASE; ?>/certificado/<?= $URL[1]; ?>">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    </div>

    <div class="htmlcontent">
        <p>Todos os cursos da <?= SITE_NAME; ?> são estruturados com fundamentos e técnicas testadas e comprovadas, formando um profissional completo e pronto para atuar no mercado.</p>
        <p>Esta página confere o certificado de <span style="text-transform: uppercase;"><?= $Student['user_name']; ?> <?= $Student['user_lastname']; ?></span>, CPF: <?= $StudentDocument; ?>, que concluiu com exito e aproveitamento as tarefas do curso <a href="<?= BASE; ?>/cursos/<?= $Course['course_name']; ?>" title="<?= $Course['course_title']; ?>" style="color: #782069;"><?= $Course['course_title']; ?></a>.</p>
        <?php
        $Read->ExeRead(DB_EAD_MODULES, "WHERE course_id = :cid", "cid={$course_id}");
        if ($Read->getResult()):
            ?>
            <h4>Os seguintes tópicos foram abordados:</h4>
            <ul>
                <?php
                foreach ($Read->getResult() AS $Modules):
                    echo "<li>{$Modules['module_title']}</li>";
                endforeach;
                ?>
            </ul>
            <?php
        endif;
        ?>

        <p>Todos os certificados emitidos pela <?= SITE_NAME; ?> são assinados digitalmente e podem ter sua autenticidade conferida em <a href="<?= BASE; ?>/certificados" title="Certificados <?= SITE_NAME; ?>"><?= BASE; ?>/certificados.</a></p>

        <div class="assinature">
            <span></span>
            EBRAHIM P. LEITE<br>
            MASTER EAD TREINAMENTOS<br>
            CNPJ: 00.000.000/0001-00
        </div>
    </div>
</article>