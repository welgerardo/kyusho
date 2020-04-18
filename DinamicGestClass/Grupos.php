<?php
/***************************************************
 SCRIPT Grupos.php V4.00
 23-09-2014
 COPYRIGHT MANUEL GERARDO PEREIRA 2014
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM

 inicio : 23-09-2014
 ultima modificação : 23-09-2014

 ******************************************************/
ini_set('display_errors', 0);
require_once 'Contacts.php';

class Grupos extends Core
{

    //array de objeto json
    private $config;

    //objecto contactos
    private $contacts;

    /**
     * Construtor
     *
     */
    public function __construct()
    {

        parent::__construct();

        $this -> config = parent::json_file("JGROUPS");
    }
    /**
     * Exporta o objeto de configuração transformado numa array php
     *
     * @return array
     *
     */
    public function get_config()
    {
        return $this -> config;
    }

    /**
     * Retira da base de dados os membros de um grupo fechado. A ordem da pesquisa é:
     * contactos.id,contactos.nome, contactos.apelido, contactos.mail, contactos.send_news, contactos.relacionamento, contactos.categoria, group_members.member.id
     *
     * @param string $group_id - chave primary que identifica o grupo
     *
     * @return objecto mysqli ou strin de erro em caso de falha
     *
     */
    private function group_members($group_id)
    {
        if (!$id = parent::id($group_id))
            $this -> mess_alert("GRP" . __LINE__);

        $members = logex::$tcnx -> query("
                                SELECT id,nome,apelido,mail,send_news,relacionamento,categoria,member_id
                                FROM contactos 
                                INNER JOIN group_members 
                                ON group_members.contact_id = contactos.id  
                                WHERE group_id = $id
                                ORDER BY nome,apelido ASC
                                ");

        if (!$members)
            $this -> mess_alert("GRP" . __LINE__);

        return $members;
    }
    
    /**
     * Devolve o nome de um grupo
     * 
     * @param string $group_id - identificador do grupo (valor da chave primária)
     * 
     * @return string - nome do grupo
     *  
     */
    public function get_group_name($group_id)
    {
        if (!$id = parent::id($group_id))
            return FALSE;
        
        if (!$group_result = logex::$tcnx -> query("SELECT nome FROM grupos WHERE id=$id LIMIT 1"))
            return FALSE;
        
        if(!$group_name = $group_result -> fetch_array())
            return FALSE;
            
        return $group_name[0];    
    }
    /**
     * Cria ficha para edição dos grupos
     * 
     * @param string $group_id - valor da chave primaria da tabela 
     *
     * @return string HTML
     *
     */
    public function edit($group_id)
    {
        if (!parent::check())
            parent::mess_alert("GRP" . __LINE__);

        if (!$id = parent::id($group_id))
            return parent::mess_alert("GRP" . __LINE__);

        if (!$group_result = logex::$tcnx -> query("SELECT * FROM grupos WHERE id=$id LIMIT 1"))
            return parent::mess_alert("GRP" . __LINE__);

        $group = $group_result -> fetch_array();

        //CONFIGURA GRUPO ABERTO
        if ($group['mode'] == "open")
        {
            $contacts = new ContactsGroup();

            $cond1 = $contacts -> get_groups_options($group['cond1']);
            $cond2 = $contacts -> get_groups_options($group['cond2']);

            $and = NULL;
            $cond = NULL;
            $values_1 = NULL;
            $values_2 = NULL;

            if ($cond1)
            {
                $cond[$group['cond1']] = $group['val1'];
                $values_1 = array($cond1 => $cond1);
            }

            if ($cond2)
            {
                $cond[$group['cond2']] = $group['val2'];
                $values_2 = array($cond2 => $cond2);
            }

            if (!$cond)
                return parent::mess_alert("GRP" . __LINE__);

            $group['all_members'] = $contacts -> get_open_group_members($cond);

            $this -> config['fields'] = $this -> config['fields_open'];

            $this -> config['fields']['members_data']['all_members']['db'] = "all_members";
            $this -> config['fields']['members_data']['option1']['db'] = "cond1";
            $this -> config['fields']['members_data']['option2']['db'] = "cond2";

            $this -> config['fields']['members_data']['select1']['db'] = "val1";
            $this -> config['fields']['members_data']['select1']['options'] = array(
                "dynamic" => array(
                    "table" => "contactos",
                    "values" => $values_1,
                    "condition" => NULL
                ),
                "static" => NULL,
                "target" => NULL
            );

            $this -> config['fields']['members_data']['select2']['db'] = "val2";
            $this -> config['fields']['members_data']['select2']['options'] = array(
                "dynamic" => array(
                    "table" => "contactos",
                    "values" => $values_2,
                    "condition" => NULL
                ),
                "static" => NULL,
                "target" => NULL
            );

            unset($this -> config['fields_close']);

        }

        //CONFIGURA GRUPO FECHADO
        if ($group['mode'] == "close")
        {

            $this -> config['fields'] = $this -> config['fields_close'];
            $this -> config['fields']['group']['members']['db'] = "group_members";

            $result = $this -> group_members($id);

            if (!is_object($result))
                return $result;

            if (!$result)
                return parent::mess_alert("GRP" . __LINE__);

            $contactos = NULL;

            while ($member = $result -> fetch_array())
            {

                $cont = ($member['nome'] || $member['apelido']) ? "$member[nome] $member[apelido]" : $member['mail'];

                $contactos .= "<p class='p100' id='pi:$member[id]' data-type='selCont,selPasta'>
                                        <img src='imagens/bt_del.png' class='igR'>
                                        $cont
                                        <input type='hidden' value='m:$member[id]'>                                       
                                        </p>";
            }

            $group['group_members'] = $contactos;
        }

        $operations = new OperationsBar($this -> config);

        return $operations -> edit_item_sheet($group);

    }

    /**
     * Ficha para adicionar um grupo
     *
     * @param string $type - tipo de grupo OPEN || CLOSE
     *
     * @return strig HTML
     */
    public function add($type)
    {

        if (!parent::check())
            parent::mess_alert("GRP" . __LINE__);

        $this -> config['fields'] = ($type == "CLOSE") ? $this -> config['fields_close'] : $this -> config['fields_open'];

        unset($this -> config['fields_close']);
        unset($this -> config['fields_open']);

        $operations = new OperationsBar($this -> config);

        return $operations -> add_item_sheet();
    }

    /**
     * Guarda os dados na base de dados
     *
     * @param $fields - array com o valor a ser guradado na tabela
     *
     * @return string json
     */
    public function save(array $fields)
    {

        if (!parent::check())
            parent::mess_alert("GRP" . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            parent::mess_alert("GRP" . __LINE__);

        if (empty($fields['group_data_title']))
            return parent::mess_alert("Tem que definir um nome para o grupo");

        $fields['public_folder'] = isset($fields['public_folder']) ? logex::$tcnx -> real_escape_string($fields['public_folder']) : NULL;
        $fields['group_data_title'] = isset($fields['group_data_title']) ? logex::$tcnx -> real_escape_string($fields['group_data_title']) : NULL;
        $fields['group_data_obs'] = isset($fields['group_data_obs']) ? logex::$tcnx -> real_escape_string($fields['group_data_obs']) : NULL;

        $save_mode = ($fields['filemode'] == "UPDATE" || $fields['filemode'] == "ADD") ? $fields['filemode'] : FALSE;

        if (!$save_mode)
            return parent::mess_alert("GRP" . __LINE__);

        //ADICIONA GRUPOS NA BASE DE DADOS
        if ($save_mode == "ADD")
        {
            //ADICIONA GRUPOS ABERTO
            if (isset($fields['members_data_option1']) && isset($fields['members_data_option2']) && isset($fields['members_data_select1']) && isset($fields['members_data_select2']))
            {
                return $this -> save_add_open($fields);
            }
            //ADICIONA GRUPOS FECHADOS
            elseif (isset($fields['members']))
            {
                return $this -> save_add_close($fields);
            }
            //MENSAGEM DE ERRO
            else
            {
                return parent::mess_alert("GRP" . __LINE__);
            }
        }

        //ATUALIZA GRUPOS NA BASE DE DADOS
        if ($save_mode == "UPDATE")
        {
            if (!$id = parent::id($fields['toke']))
                return parent::mess_alert("GRP" . __LINE__);

            //ATUALIZA GRUPOS ABERTOS
            if (isset($fields['members_data_option1']) && isset($fields['members_data_option2']) && isset($fields['members_data_select1']) && isset($fields['members_data_select2']))
            {
                return $this -> save_update_open($fields);
            }
            //ATUALIZA GRUPOS FECHADOS
            elseif (isset($fields['members']))
            {
                return $this -> save_update_close($fields);
            }
            //MENSAGEM DE ERRO
            else
            {
                return parent::mess_alert("GRP" . __LINE__);
            }

        }

        return parent::mess_alert("GRP" . __LINE__);
    }

    /**
     * Adiciona umm grupo aberto na base de dados
     *
     * @param array $fields - valor a ser guradado na tabela
     *
     * @return string json
     */
    private function save_add_open(array $fields)
    {
        #TODO melhorar verificação
        $cond1 = logex::$tcnx -> real_escape_string($fields['members_data_option1']);
        $cond2 = logex::$tcnx -> real_escape_string($fields['members_data_option2']);
        $val1 = logex::$tcnx -> real_escape_string($fields['members_data_select1']);
        $val2 = logex::$tcnx -> real_escape_string($fields['members_data_select2']);

        #INSERE CONTACTO
        if (!logex::$tcnx -> query("
                    INSERT INTO grupos 
                    (pasta,data,data_act,nome,cond1,cond2,val1,val2,mode,obs) 
                    VALUES
                    ('$fields[public_folder]','$GLOBALS[NOW]','$GLOBALS[NOW]','$fields[group_data_title]','$cond1','$cond2','$val1','$val2','open','$fields[group_data_obs]')
                    "))
        {
            if (logex::$tcnx -> errno == 1062)
                return '{"alert":"Já existe um grupo com esse nome."}';

            return parent::mess_alert("GRP" . __LINE__);
        }

        $identi = (logex::$tcnx -> insert_id) ? logex::$tcnx -> insert_id : $id;
        return '{"result":["' . $fields['public_folder'] . '","i:' . $identi . '"]}';
    }

    /**
     * Atualiza um grupo aberto na base de dados
     *
     * @param $fields - array com o valor a ser guradado na tabela
     *
     * @return json
     */
    private function save_update_open(array $fields)
    {
        #TODO melhorar verificação
        $cond1 = logex::$tcnx -> real_escape_string($fields['members_data_option1']);
        $cond2 = logex::$tcnx -> real_escape_string($fields['members_data_option2']);
        $val1 = logex::$tcnx -> real_escape_string($fields['members_data_select1']);
        $val2 = logex::$tcnx -> real_escape_string($fields['members_data_select2']);

        if (!$id = parent::id($fields['toke']))
            return parent::mess_alert("GRP" . __LINE__);

        #INSERE CONTACTO
        if (!logex::$tcnx -> query("
                    UPDATE grupos SET                    pasta='$fields[public_folder]',data_act='$GLOBALS[NOW]',nome='$fields[group_data_title]',cond1='$cond1',cond2='$cond2',val1='$val1',val2='$val2',mode='open',obs='$fields[group_data_obs]'
                    WHERE id=$id
                    "))
        {
            if (logex::$tcnx -> errno == 1062)
                return '{"alert":"Já existe um grupo com esse nome."}';

            return parent::mess_alert("GRP" . __LINE__);
        }

        $identi = (logex::$tcnx -> insert_id) ? logex::$tcnx -> insert_id : $id;
        return '{"result":["' . $fields['public_folder'] . '","i:' . $identi . '"]}';
    }

    /**
     * Adiciona umm grupo fechado na base de dados
     *
     * @param array $fields - valor a ser guradado na tabela
     *
     * @return string json
     */
    private function save_add_close(array $fields)
    {
        if (empty($fields['members']))
            return parent::mess_alert("O grupo não tem elementos. \\n Deve selecionar pelo menos 1 elemento.");

        $group = NULL;
        $error = NULL;

        #GROUP
        $gro = array_unique($fields['members']);
        $gro = preg_replace("/m:/", " ", $gro);

        $tc = new mysqli('localhost', _US, _PS, _DB);
        $tc -> set_charset("utf8");

        if (!$tc)
            return parent::mess_alert("GRP" . __LINE__);

        $tc -> autocommit(FALSE);

        //ADICIONA NA TABELA GRUPOS
        if (!$tc -> query("
                    INSERT INTO grupos 
                    (pasta,data,data_act,nome,mode,obs) 
                    VALUES
                    ('$fields[public_folder]','$GLOBALS[NOW]','$GLOBALS[NOW]','$fields[group_data_title]','close','$fields[group_data_obs]')
                    "))
        {

            if ($tc -> errno == 1062)
                $error = '{"alert":"Já existe um grupo com esse nome."}';

            $tc -> rollback();

            if ($error)
                return $error;

            return parent::mess_alert($tc -> error);
        }

        $new_group_id = $tc -> insert_id;

        foreach ($gro as $key => $value)
        {

            if (!filter_var($value, FILTER_VALIDATE_INT))
            {
                unset($gro[$key]);
                continue;
            }

            $group .= "($new_group_id,$value),";

        }

        //ADICIONA NA TABELA QUE GUARDA OS MEMBROS DE 1 GRUPO
        if (!$tc -> query("INSERT INTO group_members (group_id,contact_id) VALUES " . trim($group, ",")))
        {
            $tc -> rollback();

            return parent::mess_alert("GRP" . __LINE__);
        }

        $tc -> commit();

        return '{"result":["' . $fields['public_folder'] . '","i:' . $new_group_id . '"]}';
    }

    /**
     * Atualiza um grupo fechado na base de dados
     *
     * @param $fields - array com o valor a ser guradado na tabela
     *
     * @return json
     */
    private function save_update_close(array $fields)
    {
        if (empty($fields['members']))
            return parent::mess_alert("O grupo não tem elementos. \\n Deve selecionar pelo menos 1 elemento.");

        if (!$id = parent::id($fields['toke']))
            return parent::mess_alert("GRP" . __LINE__);

        $group = NULL;
        $error = NULL;

        #GROUP
        $gro = array_unique($fields['members']);
        $gro = preg_replace("/m:/", " ", $gro);

        $tc = new mysqli('localhost', _US, _PS, _DB);
        $tc -> set_charset("utf8");

        if (!$tc)
            return parent::mess_alert("GRP" . __LINE__);

        $tc -> autocommit(FALSE);

        //APAGA TODOS OS MEMBROS DO GRUPO NA TABELA DE MEMBROS DE 1 GRUPO
        if (!$tc -> query("DELETE FROM  group_members WHERE group_id = $id"))
        {
            $tc -> rollback();

            return parent::mess_alert("GRP" . __LINE__);
        }

        //ATUALIZA O GRUPO NA TABELA DOS GRUPOS
        if (!$tc -> query("
                    UPDATE grupos SET
                    pasta='$fields[public_folder]',data_act='$GLOBALS[NOW]',nome='$fields[group_data_title]',mode='close',obs='$fields[group_data_obs]' 
                    WHERE id = $id
                    "))
        {

            if ($tc -> errno == 1062)
                $error = '{"alert":"Já existe um grupo com esse nome."}';

            $tc -> rollback();

            if ($error)
                return $error;

            return parent::mess_alert($tc -> error);
        }

        foreach ($gro as $key => $value)
        {

            if (!filter_var($value, FILTER_VALIDATE_INT))
            {
                unset($gro[$key]);
                continue;
            }

            $group .= "($id,$value),";

        }

        //GUARDA OS MEMBROS DO GRUPO NA TABELA DE MEMBROS DE 1 GRUPO
        if (!$tc -> query("INSERT INTO group_members (group_id,contact_id) VALUES " . trim($group, ",")))
        {
            $tc -> rollback();

            return parent::mess_alert("GRP" . __LINE__);
        }

        $tc -> commit();

        return '{"result":["' . $fields['public_folder'] . '","i:' . $id . '"]}';
    }

    /**
     * Cria ficha a ficha de um grupo.
     * Se o parametro $complete estiver definido como FALSE devolve um objeto mysqli com os membros de um grupo. A ordem da pesquisa depende do tipo de grupo. 
     * Para grupos fechados a ordem é:
     * contactos.id,contactos.nome, contactos.apelido, contactos.mail, contactos.send_news, contactos.relacionamento, contactos.categoria, group_members.member.id
     * Para grupos aberto a ordem é:
     * id, nome, apelido, mail, [1ª condição], [2º condição],....,[N condição]
     * 
     * @param string $group_id - valor da chave primaria da tabela 
     * @param boolean $complete - Se FALSE devolve objeto mysqli
     * 
     * @return boolean|string HTML
     *
     */
    public function make_file($group_id,$complete=TRUE)
    {

        if (!parent::check())
            return parent::mess_alert("GRP" . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return parent::mess_alert("GRP" . __LINE__);

        if (!$id = parent::id($group_id))
            return parent::mess_alert("GRP" . __LINE__);

        if (!$group_result = logex::$tcnx -> query("SELECT * FROM grupos WHERE id=$id LIMIT 1"))
            return parent::mess_alert("GRP" . __LINE__);

        $group = $group_result -> fetch_array();

        $members = NULL;
        $result = NULL;
        $condition = NULL;
        $numb_members = 0;

        //GRUPO ABERTO
        if ($group['mode'] == "open")
        {
            $contacts = new ContactsGroup();

            $cond1 = $contacts -> get_groups_options($group['cond1']);
            $cond2 = $contacts -> get_groups_options($group['cond2']);

            $and = NULL;
            $cond = NULL;

            if ($cond1)
            {
                $condition = $cond1 . "='" . $group['val1'] . "'";
                $cond[$group['cond1']] = $group['val1'];
            }

            if ($condition)
                $and = " AND ";

            if ($cond2)
            {
                $condition .= $and . $cond2 . "='" . $group['val2'] . "'";
                $cond[$group['cond2']] = $group['val2'];
            }

            if (!$cond)
                return parent::mess_alert("GRP" . __LINE__);

            $cont = new ContactsGroup();
            
            if(!$complete)
                return  $cont->get_open_group_members_result($cond);
            
            $members = $cont -> get_open_group_members($cond);
            $numb_members = $cont -> get_numb_members();

        }

        //GRUPO FECHADO
        if ($group['mode'] == "close")
        {
            $result = $this -> group_members($id);
            
            if(!$complete)
                return $result;

            if (!$result)
                return parent::mess_alert("GRP" . __LINE__);

            if (!is_object($result))
                return $result;

            $numb_members = $result -> num_rows;

            while ($member = $result -> fetch_array())
            {
                $members .= "
                            <div class='p100R'>
                            <div class='sp25'>$member[nome] $member[apelido]</div> 
                            <div class='sp25'>$member[mail]</div> 
                            <div class='sp25'>$member[relacionamento]</div> 
                            <div class='sp25'>$member[categoria]</div>
                            </div>";
            }
        }

        //CRIA CONTEUDO DO GRUPO
        $group['content']['num_members']['type'] = "L_INPUT";
        $group['content']['num_members']['name'] = "numero de membros";
        $group['content']['num_members']['db'] = "n_members";

        $group['content']['obser']['type'] = "L_EDITDIV";
        $group['content']['obser']['name'] = "observações";
        $group['content']['obser']['db'] = "obs";

        $group['content']['num_members']['type'] = "L_INPUT";
        $group['content']['num_members']['name'] = "numero de membros";
        $group['content']['num_members']['db'] = "n_members";

        $group['content']['content']['type'] = "L_EDITDIV";
        $group['content']['content']['name'] = "membros";
        $group['content']['content']['db'] = "conteudo";

        //MANIPULA RESULTADO DA BD
        $db['n_members'] = $numb_members;
        $db['conteudo'] = $members;
        $db['obs'] = $group['obs'];
        $db['mode'] = ($group['mode'] == "open") ? "Aberto" : "Fechado";

        //CRIA A FICHA
        $itemsheet = new ItemSheet();
        $content = $itemsheet -> make_sheet_content($group, $db);

        if ($group['mode'] == "open")
        {
            $this -> config['admin']['public']['condi']['name'] = "definições";
            $this -> config['admin']['public']['condi']['db'] = "cond";

            $group['cond'] = str_replace("AND", "e", $condition);
        }

        $group['mode'] = ($group['mode'] == "open") ? "Aberto" : "Fechado";

        return $itemsheet -> make_sheet($this -> config, $group, $content);

    }

    /**
     * Apaga um grupo
     *
     * @param string $id_group - valor da chave primaria da tabela grupos
     *
     * @return string json
     *
     */
    public function delete_group($id_group)
    {
        if (!parent::check())
            return parent::mess_alert("GRP" . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return parent::mess_alert("GRP" . __LINE__);

        if (!$id = parent::id($id_group))
            return parent::mess_alert("GRP" . __LINE__);

        if (!$group_result = logex::$tcnx -> query("SELECT * FROM grupos WHERE id=$id LIMIT 1"))
            return parent::mess_alert("GRP" . __LINE__);

        $group = $group_result -> fetch_array();

        if ($group['mode'] == "open")
        {

            if (!logex::$tcnx -> query("DELETE FROM grupos WHERE id=$id"))
                return parent::mess_alert("GRP" . __LINE__);
        }

        if ($group['mode'] == "close")
        {

            $tc = new mysqli('localhost', _US, _PS, _DB);
            $tc -> set_charset("utf8");

            if (!$tc)
                return parent::mess_alert("GRP" . __LINE__);

            $tc -> autocommit(FALSE);

            //APAGA TODOS OS MEMBROS DO GRUPO NA TABELA DE MEMBROS DE UM GRUPO
            if (!$tc -> query("DELETE FROM  group_members WHERE group_id = $id"))
            {
                $tc -> rollback();
                return parent::mess_alert("GRP" . __LINE__);
            }

            if (!$tc -> query("DELETE FROM grupos WHERE id=$id"))
            {
                $tc -> rollback();
                return parent::mess_alert("GRP" . __LINE__);
            }

            $tc -> commit();

        }

        return '{"result":["' . $group['pasta'] . '","' . $id . '"]}';

    }

    /**
     * cria as notas do grupo
     * @return boolean | string
     */
    public function show_notes()
    {
        if (parent::check())
        {

            $id = $this -> id($_POST['toke']);

            if (isset($_POST['flag']) && $_POST['flag'] === "NOTES" && $id)
            {

                return $this -> do_notes($this -> config['fields']['manag']['notes']['db'], $this -> config['table'], "id", $id);

            }
            else
            {

                return FALSE;
            }
        }

    }

}

class ContactsGroup extends Contacts
{
    //array de configuração
    private $config;
    //tabela na base de dados
    private $table;
    //numero de membros de um grupo
    private $numb_members;
    //objecto mysqli da procura dos membros de um grupo
    private $open_group_members_result;

    public function __construct()
    {

        parent::__construct();

        $this -> config = parent::get_contact_config();

        $this -> table = parent::get_table();
    }
    /**
     * Devolve objecto mysqli da procura dos membros de um grupo
     * 
     * @return object mysqli
     * 
     */
    public function get_open_group_members_result($cond){
        
        return $this->get_open_group_members($cond,FALSE);
    }
    /**
     * Devolve o nome da coluna das opções para a criação de umm grupo aberto
     *
     * @return string - nome da coluna na base de dados
     *
     */
    public function get_groups_options($index)
    {
        if (!parent::check())
            return parent::mess_alert("CGRP" . __LINE__);

        return $this -> set_groups_options($index);
    }

    /**
     * Devolve o numero de membros do ultimo grupo pesquisado
     *
     * @return int
     *
     */
    public function get_numb_members()
    {
        if (!parent::check())
            return parent::mess_alert("CGRP" . __LINE__);

        return $this -> numb_members;
    }

    /**
     * Traduz os valores dos campos de opções na ficha de criação de um grupo aberto para nome das colunas na base de dados
     *
     * @return string - nome de coluna
     *
     */
    protected function set_groups_options($index)
    {

        $config = $this -> config['fields'];

        $columns["ACT"] = $config['company']['business']['db'];
        $columns["CAT"] = $this -> config['admin']['public']['category']['db'];
        $columns["REL"] = $this -> config['admin']['public']['relation']['db'];
        $columns["VIL"] = $config['comuns']['village']['db'];
        $columns["CPT"] = $config['comuns']['postal']['db'];
        $columns["CNT"] = $config['comuns']['country']['db'];
        $columns["CTY"] = $config['comuns']['city']['db'];
        $columns["GND"] = $config['personal']['sex']['db'];
        $columns["STT"] = $config['comuns']['state']['db'];
        $columns["PRF"] = $config['personal']['profession']['db'];
        $columns["CVS"] = $config['personal']['civil']['db'];

        if (!isset($columns[$index]))
            return NULL;

        return $columns[$index];
    }

    /**
     * Procura na base de dados todos os valores possiveis para determinada coluna que é opção para criação de um grupo aberto
     *
     * @param string - chave da array de tradução para nome de colunas da base de dados
     *
     * @return string - json com todos os valores possiveis para a coluna pesquisada
     *
     */
    public function get_group_options($column_index)
    {
        if (!parent::check())
            return parent::mess_alert("CGRP" . __LINE__);

        if (!$column = $this -> set_groups_options($column_index))
            return parent::mess_alert("CGRP" . __LINE__);

        if (!$result = logex::$tcnx -> query("SELECT DISTINCT $column FROM $this->table"))
            return parent::mess_alert("CGRP" . __LINE__);

        $columns = NULL;

        while ($columns_result = $result -> fetch_array())
        {

            if (!empty($columns_result[0]))
                $columns .= '"' . $columns_result[0] . '",';
        }

        return '{"result":[' . trim($columns, ",") . ']}';
    }

    /**
     * Obtem os membros de um grupo aberto. A ordem da pesquisa é:
     * id, nome, apelido, mail, [1ª condição], [2º condição],....,[N condição]
     *
     * @param array $query - colunas e valores que condicionam o grupo
     * @param boolean $complete - Se FALSE devolve o objeto mysqli
     *
     * @return string HTML com os membros do grupo
     *
     */
    public function get_open_group_members(array $query, $complete=TRUE)
    {

        $fields = NULL;
        $conditions = NULL;
        $contacts = NULL;

        foreach ($query as $key => $value)
        {
            $column = NULL;
            if (!$column = $this -> set_groups_options($key))
                continue;

            $fields .= "," . $column;

            $conditions .= " AND " . $column . "='" . $value . "'";
        }

        if (!$members_result = logex::$tcnx -> query("SELECT id,nome, apelido, mail $fields FROM contactos WHERE mail<>'' AND send_news=1 $conditions"))
            return parent::mess_alert("CON" . __LINE__);
        
        if(!$complete)
            return $members_result;

        $this -> numb_members = $members_result -> num_rows;

        while ($member = $members_result -> fetch_array())
        {
            $member_5 = null;
            $member_4 = null;

            if (isset($member[4]))
                $member_4 = $member[4];
            if (isset($member[5]))
                $member_5 = $member[5];

            $contacts .= "
                        <div class='p100R'>
                        <div class='sp25'>&nbsp;$member[1] $member[2]</div> 
                        <div class='sp25'>&nbsp;$member[3]</div> 
                        <div class='sp25'>&nbsp;$member_4</div> 
                        <div class='sp25'>&nbsp;$member_5</div>
                        </div>";
        }

        return $contacts;

    }

}

/**
 *
 */
class GroupGestFolder extends GestFolders
{

    function __construct()
    {

        $contacts = new Contacts();

        parent::__construct($contacts -> get_contact_config());
    }

    /*
     * Subescreve a função da classe mãe
     *
     */
    protected function set_item_query($folder)
    {

        $item_query = "SELECT id,nome, apelido, mail FROM contactos WHERE mail<>'' AND send_news=1 AND pasta='$folder'";

        $q_item = logex::$tcnx -> query($item_query);

        return $this -> get_folders_itens($q_item, $folder);
    }

}
?>
