<div class="wc_ead">
    <?php
    $Read->ExeRead(DB_USERS, "WHERE user_id = :user", "user={$User['user_id']}");
    if ($Read->getResult()):
        $Student = $Read->getResult()[0];
        extract($Student);

        $UpdateStudentAcess = ['user_lastaccess' => date('Y-m-d H:i:s'), 'user_login' => time()];
        $Update = new Update;
        $Update->ExeUpdate(DB_USERS, $UpdateStudentAcess, "WHERE user_id = :user", "user={$user_id}");

        //USER VARS
        $user_thumb = ($user_thumb ? "uploads/{$user_thumb}" : 'campus/_img/no_avatar.jpg');

        $Read->LinkResult(DB_USERS_ADDR, "user_id", $user_id);
        if ($Read->getResult()):
            extract($Read->getResult()[0]);
        else:
            $NewAddr = ['user_id' => $user_id, 'addr_key' => 1, 'addr_name' => "Meu Endereço"];
            $Create->ExeCreate(DB_USERS_ADDR, $NewAddr);

            $Read->LinkResult(DB_USERS_ADDR, "user_id", $user_id);
            extract($Read->getResult()[0]);
        endif;

        //COUNT CERTIFICATES
        $Read->ExeRead(DB_EAD_STUDENT_CERTIFICATES, "WHERE user_id = :user", "user={$user_id}");
        $CountCertificate = ($Read->getResult() ? $Read->getRowCount() : 0);
    endif;

    //GET FEEBACK ALERTS
    if (!empty($_SESSION['wc_ead_alert'])):
        echo "<div class='wc_ead_alert' style='display: flex;'>
    <div class='trigger_notify_box'>
    <div class='trigger_modal_box' style='display: flex;'>
    <div class='trigger_modal trigger_{$_SESSION['wc_ead_alert'][0]}' style='opacity: 1; top: 0px;'>
    <span class='icon-cross trigger_modal_close icon-notext'></span>
    <div class='trigger_modal_icon icon-sad icon-notext'></div>
    <div class='trigger_modal_content'>    
    <div class='trigger_modal_content_title'>{$_SESSION['wc_ead_alert'][1]}</div>
    <div class='trigger_modal_content_message'>{$_SESSION['wc_ead_alert'][2]}</div>
    </div></div></div></div></div>";
        unset($_SESSION['wc_ead_alert']);
    endif;
    ?>

    <header class="dashboard_header">
        <div class="dashboard_header_title">
            <h1 class="icon-trophy">Certificados</h1>
            <p class="dashboard_header_breadcrumbs">
                &raquo; <a style="font-weight:normal" href="campus.php?wc=cursos/cursos" title="<?= SITE_NAME; ?>"><?= SITE_NAME; ?></a>
                <span class="crumb">/</span>
                <a title="Certificados" href="campus.php?wc=cursos/todos">Meus Certificados</a>
            </p>
        </div>
    </header>

    <div class="box_wrap">
        <?php
        $Read->ExeRead(DB_EAD_STUDENT_CERTIFICATES, "WHERE user_id = :user", "user={$user_id}");
        if (!$Read->getResult()):
            echo "<div class='trigger trigger_none trigger_info icon-info al_center'>Você ainda não possui Certificados emitidos, {$user_name}!</div>";
        else:
            foreach ($Read->getResult() as $Certification):
                $Read->LinkResult(DB_EAD_COURSES, 'course_id', $Certification['course_id']);
                $CertificationCourse = $Read->getResult()[0];
                ?>
                <article class="box box4 dash_profile_certificates_content_cet">
                    <img alt="Certificado <?= $CertificationCourse['course_title']; ?>" title="Certificado <?= $CertificationCourse['course_title']; ?>" src="<?= BASE; ?>/tim.php?src=uploads/<?= $CertificationCourse['course_certification_cover']; ?>&w=500"/>
                    <div class="dash_profile_certificates_content_cet_content">
                        <h3 class="normalize_height_course"><?= $CertificationCourse['course_title']; ?></h3>
                        <p class="key"><i class="icon-key2 icon-notext"></i> <a title="Consultar Certificado" target="_blank" href="<?= BASE; ?>/certificado/<?= $Certification['certificate_key']; ?>"><?= $Certification['certificate_key']; ?></a></p>
                        <a class="print" style="width: 100%" title="Imprimir Certificado" href="<?= BASE; ?>/imprimir-certificados/campus.php?wc=cursos/imprimir&id=<?= $Certification['certificate_key']; ?>" target="_blank"><i class="icon-printer"></i> Imprimir Certificado</a>
                    </div>
                </article>
            <?php
            endforeach;
        endif;
        ?>
    </div>
</div>