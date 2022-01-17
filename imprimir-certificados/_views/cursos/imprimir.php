<div class="wc_ead">
    <style>
        .dashboard_sidebar{display: none;}

        /*CERTIFICATION*/
        .wc_ead_certificate{
            width: 3508px;
            position: relative;
            left: 0;
            top: 0;
            background: #fff;
        }

        .wc_ead_certificate_name{
            position: absolute;
            left: 1240px;
            top: 800px;
            font-size: 110px;
            font-weight: 600;
            line-height: 0;
            text-transform: uppercase;
            font-family: 'Open Sans',serif
        }

        .wc_ead_certificate_course{
            position: absolute;
            left: 1240px;
            top: 1310px;
            font-size: 90px;
            line-height: 0;
            text-transform: uppercase;
            font-family: 'Open Sans',serif
        }

        .wc_ead_certificate_workload{
            position: absolute;
            left: 1240px;
            top: 1550px;
            font-size: 65px;
            line-height: 0;
            text-transform: uppercase;
            color: #555;
            font-family: 'Open Sans',serif
        }

        .wc_ead_certificate_validate{
            position: absolute;
            left: 445px;
            top: 1580px;
            font-weight: normal;
            font-size: 40px;
            line-height: 0;
            text-transform: uppercase;
            color: #555;
            font-family: 'Open Sans',serif
        }

        .wc_ead_certificate_period{
            position: absolute;
            left: 1245px;
            top: 1710px;
            font-weight: normal;
            font-size: 40px;
            line-height: 0;
            text-transform: uppercase;
            color: #555;
            font-family: 'Open Sans',serif
        }

        .wc_ead_certificate_date{
            position: absolute;
            left: 1245px;
            top: 1850px;
            font-weight: normal;
            font-size: 40px;
            line-height: 0;
            text-transform: uppercase;
            color: #555;
            font-family: 'Open Sans',serif
        }

        .wc_ead_certificate_overload{
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            display: flex;
            background: #000;
        }

        .wc_ead_certificate_overload_box{
            display: block;
            color: #fff;
            text-align: center;
            margin: auto;
            font-family: 'Open Sans',serif
        }

        .wc_ead_certificate_overload_box p{
            font-size: 1em;
            font-weight: 500;
            margin-top: 15px;
            text-transform: uppercase;
            font-family: 'Open Sans',serif
        }
    </style>
    <?php
    if (empty($_SESSION['userLogin'])):
        header("Location: " . BASE . "/campus");
    endif;

    $CertificateKey = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (empty($CertificateKey)):
        $_SESSION['wc_ead_alert'] = ["red", "Erro ao acessar {$_SESSION['userLogin']['user_name']},", "É preciso informar a chave de autenticação do certificado!"];
        header("Location: " . BASE . "/campus/campus.php?wc=cursos/home");
        exit;
    else:
        if (!$Read):
            $Read = new Read;
        endif;

        $Read->ExeRead(DB_EAD_STUDENT_CERTIFICATES, "WHERE user_id = :user AND certificate_key = :key AND certificate_status = 1", "user={$_SESSION['userLogin']['user_id']}&key={$CertificateKey}");
        if (!$Read->getResult()):
            $_SESSION['wc_ead_alert'] = ["red", "Erro ao acessar {$_SESSION['userLogin']['user_name']},", "O certificado solicitado não existe ou é restrito!"];
            header("Location: " . BASE . "/campus/campus.php?wc=cursos/home");
        else:
            extract($Read->getResult()[0]);

            $Read->ExeRead(DB_EAD_COURSES, "WHERE course_id = :course", "course={$course_id}");
            extract($Read->getResult()[0]);

            //ENROLLMENTS
            $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :cid AND user_id = :uid", "cid={$course_id}&uid={$user_id}");
            $Enrollment = $Read->getResult() ? $Read->getResult()[0] : null;

            date_default_timezone_set('America/Sao_Paulo');
            $date = date('Y-m-d H:i');
            ?>
            <div class="wc_ead_certificate">
                <img alt="Certificado do Curso <?= $course_title; ?>" title="Certificado do Curso <?= $course_title; ?>" src="<?= BASE; ?>/uploads/<?= $course_certification_mockup; ?>"/>
                <p class="wc_ead_certificate_name"><?= "{$_SESSION['userLogin']['user_name']} {$_SESSION['userLogin']['user_lastname']}"; ?></p>
                <p class="wc_ead_certificate_course"><?= $course_title; ?></p>
                <p class="wc_ead_certificate_workload">Carga Horária: <?= $course_certification_workload; ?> Horas</p>
                <p class="wc_ead_certificate_validate">CPF: <?= $_SESSION['userLogin']['user_document']; ?> / AUTENTICAÇÃO: <?= $certificate_key; ?></p>
                <p class="wc_ead_certificate_period">Período de: <?= date("d/m/Y", strtotime($Enrollment['enrollment_start'])); ?> a <?= date("d/m/Y", strtotime($certificate_issued)); ?></p>
                <p class="wc_ead_certificate_date"><?= SITE_ADDR_CITY; ?>/<?= SITE_ADDR_UF; ?>, <time datetime="<?= date('Y-m-d', strtotime($date)); ?>" pubdate="pubdate"><?= date('d/m/Y \à\s H\hi', strtotime($date)); ?></time></p>
            </div>

            <div class="wc_ead_certificate_overload">
                <div class="wc_ead_certificate_overload_box">
                    <img src="<?= BASE; ?>/campus/_img/load.svg" alt="Emitindo Certificado" title="Emitindo Certificado!"/>
                    <p>Validando Certificado, Aguarde!</p>
                </div>
            </div>

            <script src="<?= BASE; ?>/_cdn/jspdf.min.js"></script>
            <script>
                $(function () {
                    var pdf = new jsPDF({
                        orientation: 'landscape'
                    });
                    pdf.addHTML($('.wc_ead_certificate'), function () {
                        //pdf.output('datauri');
                        pdf.save("certificado-<?= $course_name; ?>.pdf");

                        $('.wc_ead_certificate_overload_box img').fadeOut(function () {
                            $('.wc_ead_certificate_overload_box p').html("Certificado Emitido com sucesso!");
                        });
                        window.close();
                        window.history.back();
                    });
                });
            </script>
        <?php
        endif;
    endif;
    ?>
</div>