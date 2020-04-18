<?php
/**
 * script: Grupos.php
 * client: EPKyusho
 *
 * @version V4.02.110615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */
ini_set('display_errors', 1);
require_once 'Contacts.php';
/**
 * Esta classe faz a gestão dos grupos para enviar as newsletters.
 * 
 * Stored procedures necessárias:
 * - spGroupData
 * - spInsertGroup
 * - spInsertGroupClose
 * - spUpdateGroup
 * - spUpdateGroupClose
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version V1.01.110615
 * @since 11/06/2015
 * @license Todos os direitos reservados
 */
class Grupos extends Core
{

    //array de objeto json
    private $config;
    //objecto contactos
    private $contacts;
    //ligação á base de dados
    private $tcnx;
    
    /**
     * Códido de erro da classe
     * 
     * @var string 
     */
    private $exp_prefix = "GRP";

    /**
     * Construtor
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->config = parent::json_file("JGROUPS");

        $this->tcnx = new mysqli(_LC, _US, _PS, _DB);
        $this->tcnx->set_charset("utf8");
    }

    /**
     * Exporta o objeto de configuração transformado numa array php
     *
     * @return array
     *
     */
    public function get_config()
    {
        return $this->config;
    }

    /**
     *
     */
    public function get_notes_table()
    {
        return $this->config['notes'];
    }

    /**
     * !Obsoleto
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
            $this->mess_alert("GRP" . __LINE__);

        $members = $this->tcnx->query("
                                SELECT id,nome,apelido,mail,send_news,relacionamento,categoria,member_id
                                FROM contactos
                                INNER JOIN group_members
                                ON group_members.contact_id = contactos.id
                                WHERE group_id = $id
                                ORDER BY nome,apelido ASC
                                ");

        if (!$members)
            $this->mess_alert("GRP" . __LINE__);

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

        if (!$group_result = $this->tcnx->query("SELECT nome FROM grupos WHERE id=$id LIMIT 1"))
            return FALSE;

        if (!$group_name = $group_result->fetch_array())
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
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($group_id))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $group_result = parent::make_call("spGroupData", array($id));
        } 
        catch (Exception $exp)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        $group = $group_result[0];

        if (!count($group))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        //CONFIGURA GRUPO ABERTO
        if ($group['mode'] == "open")
        {
            $contacts = new ContactsGroup();

            //nome das colunas na tabela contactos que condicionam o resultado
            $cond1 = $contacts->get_groups_options($group['cond1']);
            $cond2 = $contacts->get_groups_options($group['cond2']);

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
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $group['all_members'] = $contacts->get_open_group_members($cond);

            $this->config['content'] = $this->config['fields_open'];

            $this->config['content']['members_data']['all_members']['db'] = "all_members";
            $this->config['content']['members_data']['option1']['db'] = "cond1";
            $this->config['content']['members_data']['option2']['db'] = "cond2";

            $this->config['content']['members_data']['select1']['db'] = "val1";
            $this->config['content']['members_data']['select1']['options'] = array(
                "dynamic" => array(
                    "table" => "contactos",
                    "values" => $values_1,
                    "condition" => NULL
                ),
                "static" => NULL,
                "target" => NULL
            );

            $this->config['content']['members_data']['select2']['db'] = "val2";
            $this->config['content']['members_data']['select2']['options'] = array(
                "dynamic" => array(
                    "table" => "contactos",
                    "values" => $values_2,
                    "condition" => NULL
                ),
                "static" => NULL,
                "target" => NULL
            );

            unset($this->config['fields_close']);
        }

        //CONFIGURA GRUPO FECHADO
        if ($group['mode'] == "close")
        {

            $this->config['content'] = $this->config['fields_close'];
            $this->config['content']['group']['members']['db'] = "group_members";

            try
            {
                $result = parent::make_call("spGroupMembers", array($id));
            } catch (Exception $exp)
            {
                return parent::mess_alert("GRP" . __LINE__);
            }


            $contactos = NULL;

            foreach ($result as $member)
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

        $operations = new OperationsBar($this->config);
        $operations->set_mode("UPDATE");
        return $operations->edit_item_sheet($group);
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
            parent::mess_alert($this->exp_prefix . __LINE__);

        $this->config['content'] = ($type == "CLOSE") ? $this->config['fields_close'] : $this->config['fields_open'];

        unset($this->config['fields_close']);
        unset($this->config['fields_open']);

        $operations = new OperationsBar($this->config);

        return $operations->add_item_sheet();
    }

    /**
     * Guarda os grupos dados na base de dados
     *
     * @param $fields - array com o valor a ser guardado na tabela
     *
     * @return string json
     */
    public function save(array $fields)
    {

        if (!parent::check())
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (!in_array($fields['flag'], $GLOBALS['FLAGSMODE']))
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (empty($fields['filemode']))
            parent::mess_alert($this->exp_prefix . __LINE__);

        if (empty($fields['group_data_title']))
            return parent::mess_alert("Tem que definir um nome para o grupo");

        $save_mode = ($fields['filemode'] == "UPDATE" || $fields['filemode'] == "ADD") ? $fields['filemode'] : FALSE;

        if (!$save_mode)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        unset($fields['filemode']);
        unset($fields['flag']);
        unset($fields['public_date']);
        unset($fields['public_data_act']);
        unset($fields['module']);

        #TODO melhorar verificação
        $folder = isset($fields['public_folder']) ? $fields['public_folder'] : "";
        $title = isset($fields['group_data_title']) ? $fields['group_data_title'] : "";
        $obs = isset($fields['group_data_obs']) ? $fields['group_data_obs'] : "";
        $cond1 = isset($fields['members_data_option1']) ? $fields['members_data_option1'] : "";
        $cond2 = isset($fields['members_data_option2']) ? $fields['members_data_option2'] : "";
        $val1 = isset($fields['members_data_select1']) ? $fields['members_data_select1'] : "";
        $val2 = isset($fields['members_data_select2']) ? $fields['members_data_select2'] : "";


        //ADICIONA GRUPOS NA BASE DE DADOS
        if ($save_mode == "ADD")
        {
            //ADICIONA GRUPOS ABERTO
            if (array_key_exists('members_data_option1',$fields) && array_key_exists('members_data_option2',$fields))
            {
                try
                {
                    $params = array($folder, $GLOBALS['NOW'], $title, $cond1, $cond2, $val1, $val2, 'open', $obs);
                    return $this->save_group("spInsertGroup", $params);
                }
                catch (Exception $exp)
                {
                    return parent::mess_alert($exp->getMessage());
                }
            }
            //ADICIONA GRUPOS FECHADOS
            elseif (isset($fields['members']))
            {
                try
                {
                    $g_members = $this->save_members_close_group($fields['members']);
                    $params = array($folder, $GLOBALS['NOW'], $title, $cond1, $cond2, $val1, $val2, 'close', $obs,$g_members);
                    return $this->save_group("spInsertGroupClose",$params);
                }
                catch (Exception $exp)
                {
                    return parent::mess_alert($exp->getMessage());
                }
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
                return parent::mess_alert($this->exp_prefix . __LINE__);

            //ATUALIZA GRUPOS ABERTOS
            if (isset($fields['members_data_option1']) && isset($fields['members_data_option2']) && isset($fields['members_data_select1']) && isset($fields['members_data_select2']))
            {
                try
                {
                    $params = array($folder, $GLOBALS['NOW'], $title, $cond1, $cond2, $val1, $val2, 'open', $obs, $id);
                    return $this->save_group("spUpdateGroup", $params);
                }
                catch (Exception $exp)
                {
                    return parent::mess_alert($exp->getMessage());
                }
            }
            //ATUALIZA GRUPOS FECHADOS
            elseif (isset($fields['members']))
            {
                try
                {
                    $g_members = $this->save_members_close_group($fields['members']);
                    $params = array($folder, $GLOBALS['NOW'], $title, $cond1, $cond2, $val1, $val2, 'close', $obs,$g_members,$id);
                    return $this->save_group("spUpdateGroupClose",$params);
                }
                catch (Exception $exp)
                {
                    return parent::mess_alert($exp->getMessage());
                }
            }
            //MENSAGEM DE ERRO
            else
            {
                return parent::mess_alert($this->exp_prefix . __LINE__);
            }
        }

        return parent::mess_alert($this->exp_prefix . __LINE__);
    }

    /**
     * Atualiza ou insere um grupo na base de dados
     *
     * @param string $procedure - nome da stored procedure a ser usada
     * @param array $params - array com os parametros da stored procedure
     *
     * @return json
     */
    private function save_group($procedure,array $params)
    {
        $result = NULL;

        if (!$procedure)
            throw new Exception($this->exp_prefix . __LINE__);

        if (!$params)
            throw new Exception($this->exp_prefix . __LINE__);

        $result = parent::make_call($procedure, $params);

        if (!$ret = json_decode($result[0]['ret'], TRUE))
            throw new Exception($this->exp_prefix . __LINE__);

        if (isset($ret['mgp_error']))
            throw new Exception($this->exp_prefix . __LINE__);

        if (empty($ret['id']))
            throw new Exception($this->exp_prefix . __LINE__);

        return '{"result":["' . $ret['pasta'] . '","i:' . $ret['id'] . '"]}';
    }

    /**
     * Cria uma string para inserir os membros de um grupo na tabela de membros de um grupo
     * Esta string depois é enviado como parametro para uma stored procedure qua guarda os dados de um grupo na base de dados
     * @tok é o nome da variável sql que guarda o id do grupo
     *
     * @param array $members - membros do grupo a ser guardado
     *
     * @return string json
     */
    private function save_members_close_group(array $members)
    {
        if (empty($members))
            throw new Exception("O grupo não tem elementos. \\n Deve selecionar pelo menos 1 elemento.");

        $group = NULL;

        #GROUP
        $gro = array_unique($members);
        $gro = preg_replace("/m:/", " ", $gro);

        foreach ($gro as $key => $value)
        {
            if (!filter_var($value, FILTER_VALIDATE_INT))
            {
                unset($gro[$key]);
                continue;
            }

            $group .= "(@tok,$value),";
        }

        return trim($group, ",");
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
    public function make_file($group_id, $complete = TRUE)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($group_id))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $group_result = parent::make_call("spGroupData", array($id));
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        $group = $group_result[0];

        $members = NULL;
        $result = NULL;
        $condition = NULL;
        $numb_members = 0;

        //GRUPO ABERTO
        if ($group['mode'] == "open")
        {
            $contacts = new ContactsGroup();

            $cond1 = $contacts->get_groups_options($group['cond1']);
            $cond2 = $contacts->get_groups_options($group['cond2']);

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
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $cont = new ContactsGroup();

            if (!$complete)
                return $cont->get_open_group_members_result($cond);

            $members = $cont->get_open_group_members($cond);
            $numb_members = $cont->get_numb_members();
        }

        //GRUPO FECHADO
        if ($group['mode'] == "close")
        {
            try
            {
                $result = parent::make_call("spGroupMembers", array($id));
                if (!$complete)
                    return $result;
            }
            catch (Exception $exp)
            {
                return parent::mess_alert($this->exp_prefix . __LINE__);
            }

            $numb_members = count($result);

            //membros do grupo
            foreach ($result as $member)
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
        $content = $itemsheet->make_sheet_content($group, $db);

        if ($group['mode'] == "open")
        {
            $this->config['admin']['public']['condi']['name'] = "definições";
            $this->config['admin']['public']['condi']['db'] = "cond";

            $group['cond'] = str_replace("AND", "e", $condition);
        }

        $group['mode'] = $db['mode'];

        return $itemsheet->make_sheet($this->config, $group, $content);
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

        if (!$group_result = $this->tcnx->query("SELECT * FROM grupos WHERE id=$id LIMIT 1"))
            return parent::mess_alert("GRP" . __LINE__);

        $group = $group_result->fetch_array();

         if (!$this->tcnx->query("DELETE FROM grupos WHERE id=$id"))
                return parent::mess_alert("GRP" . __LINE__);

        return '{"result":["' . $group['pasta'] . '","' . $id . '"]}';
    }

    /**
     * cria as notas do grupo
     * @return boolean | string
     */
    public function show_notes()
    {

        if (!parent::check())
            return NULL;

        if (!$id = $this->id($_POST['toke']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $notes = new GestNotes();
        return $notes->show_notes($this->config['notes'], $id);
    }

}
/**
 * Esta classe faz a gestão dos contacto em grupos para enviar as newsletters.
 * 
 * Stored procedures necessárias:
 * - spSelectContactsOptions
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version V1.00.030615
 * @since 03/06/2015
 * @license Todos os direitos reservados
 */
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

    /**
     * código de erro da classe
     */
    private $exp_prefix = "CGRP";

    public function __construct()
    {

        parent::__construct();

        $this->config = parent::get_contact_config();

        $this->table = parent::get_table();
    }

    /**
     * Devolve objecto mysqli da procura dos membros de um grupo
     *
     * @return object mysqli
     *
     */
    public function get_open_group_members_result($cond)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return $this->get_open_group_members($cond, FALSE);
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
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return $this->set_groups_options($index);
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
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return $this->numb_members;
    }

    /**
     * Traduz os valores dos campos de opções na ficha de criação de um grupo aberto para nome das colunas na base de dados
     *
     * @return string - nome de coluna
     *
     */
    protected function set_groups_options($index)
    {

        $config = $this->config['content'];

        $columns["ACT"] = $config['data_company']['business']['db'];
        $columns["CAT"] = $this->config['admin']['public']['category']['db'];
        $columns["REL"] = $this->config['admin']['public']['group']['db'];
        $columns["VIL"] = $config['local']['village']['db'];
        $columns["CPT"] = $config['local']['postal']['db'];
        $columns["CNT"] = $config['local']['country']['db'];
        $columns["CTY"] = $config['local']['city']['db'];
        $columns["GND"] = $config['data']['sex']['db'];
        $columns["STT"] = $config['local']['state']['db'];
        $columns["PRF"] = $config['data']['profession']['db'];
        $columns["CVS"] = $config['data']['civil']['db'];

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
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$column = $this->set_groups_options($column_index))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$result = parent::make_call("spSelectContactsOptions", array($column)))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $columns = NULL;

        foreach ($result as $columns_result)
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
     * @param boolean $complete - Se FALSE devolve array com o resultado da pesquisa na base de dados
     *
     * @return string HTML com os membros do grupo
     *
     */
    public function get_open_group_members(array $query, $complete = TRUE)
    {

        $fields = NULL;
        $conditions = NULL;
        $contacts = NULL;

        foreach ($query as $key => $value)
        {
            $column = NULL;

            if (!$column = $this->set_groups_options($key))
                continue;

            $fields .= "," . $column.' as "'.$column.'"';

            $conditions .= ' AND ' . $column . '="' . $value . '"';
        }

        $dbcon = new PDO(_PDOM, _US, _PS);
        
        
        $sttm = $dbcon->prepare("CALL spOpenGroupMembers(?,?)");
        
        $sttm->bindParam(1, $fields, PDO::PARAM_STR);
        $sttm->bindParam(2, $conditions, PDO::PARAM_STR);
        
        
        $sttm->execute();
        
        $members_result = $sttm->fetchAll();
        
        if ($members_result===FALSE)
            return parent::mess_alert( $this->exp_prefix . __LINE__);

        if (!$complete)
            return $members_result;


        $all_members = $members_result;

        foreach ($all_members as $member)
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

?>
