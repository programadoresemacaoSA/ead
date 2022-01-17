<?php
/*
 * Configura o timezone do Servidor para São Paulo (caso necessite de outro, basta alterar aqui)
 * OBS: Isso está aqui para garantir o uso correto da contagem na página de espera.
 */
date_default_timezone_set('America/Sao_Paulo');

/*
 * ******************************
 * ********* FUNCTIONS **********
 * ******************************
 */

/**
 * save_lead: Cria ou atualiza os usuários na tabela de leads conforme os dados solicitados.
 *
 * @param string $name
 * @param string $email
 * @param string $CourseId
 * @return array|null
 */
function save_lead(string $name, string $email, string $CourseId)
{
    //Chamada das classes necessárias
    $Read = new Read;
    $Create = new Create;
    $Update = new Update;
    $active = new WorkActiveCampaign;

    $Read->FullRead("SELECT course_id, course_verification_type, course_tag, course_list FROM " . DB_EAD_COURSES . " WHERE course_id = :nm",
        "nm={$CourseId}");
    if ($Read->getResult()) {
        $CourseId = $Read->getResult()[0]['course_id'];
        $vOption = $Read->getResult()[0]['course_verification_type'];
        $vTag = $Read->getResult()[0]['course_tag'];
        $listId = $Read->getResult()[0]['course_list'];
    } else {
        return false;
    }

    $Read->ExeRead(DB_EAD_COURSES_LEAD, "WHERE course_lead_email = :email", "email={$email}");
    if (!$Read->getResult()) {
        //Caso o usuário não tenha cadastro como lead ainda, pegamos os dados dele e registramos como lead para termos uma base de dados coesa com os interessados nas lives
        $ArrLead = [
            'course_lead_name' => $name,
            'course_lead_email' => $email,
            'course_lead_date' => date('Y-m-d H:i:s'),
            'course_lead_lastupdate' => date('Y-m-d H:i:s')
        ];

        $Create->ExeCreate(DB_EAD_COURSES_LEAD, $ArrLead);

        //Cadastro do workshop vinculado ao lead
        $arrLives = [
            'course_lead_id' => $Create->getResult(),
            'course_id' => $CourseId,
            'course_leads_status' => 'yes'
        ];

        $Create->ExeCreate(DB_EAD_COURSES_LEADS, $arrLives);

        //Após criado o registro do lead no banco, puxamos os dados e criamos a variável para a sessão de Login no Workshop
        $Read->ExeRead(DB_EAD_COURSES_LEAD, "WHERE course_lead_email = :email", "email={$email}");
        if ($Read->getResult()) {
            //Active Campaing
            if ($vOption == "ActiveCampaign" && (!empty(LIVE_AC_HOST) && !empty(LIVE_AC_APIKEY))) {
                //Trata as strings dos nomes
                if (strpos($name, " ")) {
                    $firstName = mb_strstr($name, " ", true);
                    $lastName = str_replace(" ", "", mb_strrchr($name, " "));
                } else {
                    $firstName = $name;
                    $lastName = null;
                }
                $acListId = (!empty($listId) ? $listId : LIVE_AC_LIST);
                $acTag = (!empty($vTag) ? $vTag : $CourseId);

                //Atualiza/cadastra o contato na respectiva lista no ActiveCampaign
                $active->addActive($email, [$acListId], $acTag, $firstName, $lastName);
            }

            return $Read->getResult()[0];
        } else {
            return false;
        }
    } else {
        $leadId = $Read->getResult()[0]['course_lead_id'];

        //Aqui verificamos os workshops assistidos pelo lead
        $Read->FullRead("SELECT course_leads_id FROM " . DB_EAD_COURSES_LEADS . " WHERE course_id = :id AND course_lead_id = :lead",
            "id={$CourseId}&lead={$leadId}");
        if (!$Read->getResult()) {
            //Cadastro do workshop vinculado ao lead
            $arrLives = [
                'course_lead_id' => $leadId,
                'course_id' => $CourseId,
                'course_leads_status' => 'yes'
            ];

            $Create->ExeCreate(DB_EAD_COURSES_LEADS, $arrLives);
        }

        //Array com os dados para atualizar o lead no banco de dados
        $ArrLead = [
            'course_lead_name' => $name,
            'course_lead_lastupdate' => date('Y-m-d H:i:s')
        ];

        //Atualizamos o usuário no banco com os dados obtidos e tratados
        $Update->ExeUpdate(DB_EAD_COURSES_LEAD, $ArrLead, "WHERE course_lead_email = :email", "email={$email}");

        //Após atualizado, puxamos os dados e criamos a variável para a sessão de Login no Workshop
        $Read->ExeRead(DB_EAD_COURSES_LEAD, "WHERE course_lead_email = :email", "email={$email}");
        if ($Read->getResult()) {
            //Active Campaing
            if ($vOption == "ActiveCampaign" && (!empty(LIVE_AC_HOST) && !empty(LIVE_AC_APIKEY))) {
                //Trata as strings dos nomes
                if (strpos($name, " ")) {
                    $firstName = mb_strstr($name, " ", true);
                    $lastName = str_replace(" ", "", mb_strrchr($name, " "));
                } else {
                    $firstName = $name;
                    $lastName = null;
                }
                $acListId = (!empty($listId) ? $listId : LIVE_AC_LIST);
                $acTag = (!empty($vTag) ? $vTag : $CourseId);

                //Atualiza/cadastra o contato na respectiva lista no ActiveCampaign
                $active->addActive($email, [$acListId], $acTag, $firstName, $lastName);
            }

            return $Read->getResult()[0];
        } else {
            return false;
        }
    }
}

/**
 * Aqui cria norificação que aparece no canto superior direito.
 *
 * @param $message
 * @param $icon
 * @param $color
 * @param null $time
 * @param null $location
 * @return array
 */
function notify($message, $icon, $color, $time = null, $location = null)
{
    return [
        'message' => $message,
        'icon' => $icon,
        'color' => $color,
        'type' => 'notify',
        'time' => ($time ? $time : 5000),
        'location' => ($location ? $location : null)
    ];
}

/**
 * Aqui cria norificação que aparece em forma de modal no centro da tela.
 *
 * @param $title
 * @param $message
 * @param $icon
 * @param $color
 * @param null $location
 * @return array
 */
function modal($title, $message, $icon, $color, $location = null)
{
    return [
        'title' => $title,
        'message' => $message,
        'icon' => $icon,
        'color' => $color,
        'type' => 'modal',
        'location' => ($location ? $location : null)
    ];
}