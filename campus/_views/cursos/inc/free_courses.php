<article class='box box5 dash_view_course'>
    <?php
    //VALID ENROLLMENT
    $Read->ExeRead(DB_EAD_ENROLLMENTS, "WHERE course_id = :course AND user_id = :user", "course={$course_id}&user={$user_id}");
    $CourseFree = ($course_free != 0 ? 'curso-gratuito' : 'curso');
    if (!$Read->getResult()):
        echo "<div class='apl-card vertical course locked j_locked'>";
        echo "<div style='color:{$segment_color}' class='wc_ead_home_courses_certifications wc_tooltip dash_view_course_certificate {$segment_icon} icon-notext'><span class='wc_tooltip_balloon'>{$segment}</span></div>";
        echo "<a href='". BASE . "/{$CourseFree}/{$course_name}' alt='Conheça o Curso {$course_title}' title='Conheça o Curso {$course_title}' target='_blank'>";
        echo "<div class='coverlink'></div>";
        echo "</a>";
        echo "<div class='card-header' style='background: {$course_color};'>";
        echo "<div class='wrapper'>";
        echo "<img class='brand' src='" . BASE . "/tim.php?src={$course_cover}&w=300&h=100' alt='Conheça o Curso {$course_title}' title='Conheça o Curso {$course_title}'>";
        echo "<h1 class='title'>{$course_title}</h1>";
        echo "<div class='status locked cadeado'></div>";
        echo "<div class='al_center'>";
        echo "<a href='". BASE . "/{$CourseFree}/{$course_name}' class='button-more'  target='_blank'>Conhecer</a>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    else:
        echo "<div class='apl-card vertical course'>";
        echo "<div style='color:{$segment_color}' class='wc_ead_home_courses_certifications wc_tooltip dash_view_course_certificate {$segment_icon} icon-notext'><span class='wc_tooltip_balloon'>{$segment}</span></div>";
        echo "<a href='campus.php?wc=cursos/estudar&id={$course_id}' title='Ir para o Curso {$course_title}' alt='Ir para o Curso {$course_title}'>";
        echo "<div class='coverlink'></div>";
        echo "</a>";
        echo "<div class='card-header' style='background: {$course_color}'>";
        echo "<div class='wrapper'>";
        echo "<img class='brand' src='" . BASE . "/tim.php?src={$course_cover}&w=300&h=100' alt='Conheça o Curso {$course_title}' title='Conheça o Curso {$course_title}'>";
        echo "<h1 style='display: none'>{$course_title}</h1>";
        echo "</div>";
        echo "</div>";
        echo "<div class='card-footer '>";
        echo "<div class='status {$CourseCompletedClass}'></div>";
        echo "<p>{$ClassStudenCount} / {$ClassCount} aulas completas</p>";
        echo "<div class='apl-progress tiny'>";
        echo "<div class='progress-course' data-width='{$CourseCompletedPercent}' style='background-color: #3BB75D; width:{$CourseCompletedPercent}%'></div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    endif;
    ?>
</article>