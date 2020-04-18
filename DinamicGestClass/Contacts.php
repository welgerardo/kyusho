<?php

/***********************************************

 SCRIPT Contacts.php V1.10

 COPYRIGHT MANUEL GERARDO PEREIRA 2014
 TODOS OS DIREITOS RESERVADOS
 CONTACTO: GPEREIRA@MGPDINAMIC.COM
 WWW.MGPDINAMIC.COM

 inicio : 11-06-2014
 ultima modificação : 11-06-2014
 *
 * objetos requeridos:
 *  JEMPLOYEES

 *******************************************/

ini_set('display_errors', 1);
require_once 'Core.php';

class Contacts extends Core
{

    #array de configuração
    private $contjs;

    #nome da tabela
    private $table;

    #nome da coluna primary key na base de dados
    private $idb;

    #nome da coluna do tipo de contacto
    private $typedb;

    public function __construct()
    {

        parent::__construct();

        $this -> contjs = $this -> json_file("JCONTACTS");

        $this -> table = $this -> contjs['table'];

        $this -> idb = $this -> contjs['admin']['private']['toke']['db'];

        $this -> typedb = $this -> contjs['admin']['public']['category']['db'];
    }

    /**
     * Retorna a array de configuração dos contatos
     *
     * @return array
     *
     */
    public function get_contact_config()
    {

        return $this -> contjs;
    }

    public function get_table()
    {

        return $this -> table;
    }

    /**
     *
     */
    public function get_notes_table()
    {

        return $this -> contjs['admin']['private']['notes']['db'];
    }

    /**
     * Procur na base de dados um grupo de contatos que permitem receber a newsletter e tem email definido. Retorna o id e o email pela ordem : id, mail
     *
     * @param string || array $contacts - string do inteiros separados por virgulas ou um array com valores inteiros que sejam o valor da chave primária
     *
     * @return object - objeto mysqli
     *
     */
    public function query_contacts_group($contacts)
    {

        #VALIDA OS CONTATOS
        $conts = (is_array($contacts)) ? $contacts : explode(",", $contacts);

        if (!is_array($conts))
            return FALSE;

        $conts = array_unique($conts);

        $group_members = implode(",", array_filter($conts, "Core::validate_int"));

        $id = $this -> idb;
        $send = $this -> contjs['admin']['public']['send_news']['db'];
        $mail = $this -> contjs['fields']['comuns']['mail']['db'];

        if (!$result = logex::$tcnx -> query("SELECT $id,$mail FROM " . $this -> table . " WHERE $id IN($group_members) AND $send=1 AND $mail <>''"))
            return FALSE;

        return $result;

    }

    /**
     * Procura um contato na base de dados
     *
     * @param string $id_contact - id do contato
     *
     * @return array - resultado da pesquisa na base de dados
     *
     */
    protected function query_contact($id_contact)
    {

        if (!$id = $this -> id($id_contact))
            return FALSE;

        if (!$result = logex::$tcnx -> query("SELECT * FROM " . $this -> table . " WHERE " . $this -> idb . "=" . $id . " LIMIT 1"))
            return FALSE;

        $contact = $result -> fetch_array();

        mysqli_free_result($result);

        return $contact;

    }

    /**
     * Cria ficha de edição do contacto
     *
     * @param string $id_contact - id do contacto
     *
     * @return string - estrtura HTML com a ficha de edição de um contacto
     *
     */
    public function edit_contact($id_contact)
    {

        if (!parent::check())
            return parent::mess_alert("CON" . __LINE__);

        if (!$contact_data = $this -> query_contact($id_contact))
            return parent::mess_alert("CON" . __LINE__);

        #VERIFICA A CATEGORIA
        $contact_type = FALSE;

        if (isset($contact_data[$this -> typedb]))
            $contact_type = strtolower($contact_data[$this -> typedb]);

        if (!$contact_type)
            return parent::mess_alert("CON" . __LINE__);

        $this -> configure_operations_sheet($contact_type);

        $edit_sheet = new OperationsBar($this -> contjs);

        return $edit_sheet -> edit_item_sheet($contact_data);
    }

    /**
     * Cria ficha para adição de um novo contato
     *
     * @param string $c_type - tipo de contato (pessoa ou empresa)
     *
     * @return string - estrutura html com a ficha
     *
     */
    public function add_contact($c_type)
    {

        if (!parent::check())
            return parent::mess_alert("CON" . __LINE__);

        $contact_type = (strtolower($c_type) === "empresa") ? "empresa" : "pessoa";

        $this -> configure_operations_sheet($contact_type);

        $predef[$this -> typedb] = $contact_type;

        $add_sheet = new OperationsBar($this -> contjs);

        return $add_sheet -> add_item_sheet($predef);

    }

    /**
     * Cria a array de configuração para criar uma ficha de operações em conformidade com o tipo de contato
     *
     * @param string $type_contact - tipo do contato (pessoa ou empresa)
     *
     * @return array - com a configuração
     *
     */
    protected function configure_operations_sheet($type_contact)
    {

        $type = (strtolower($type_contact) === "empresa") ? "empresa" : "pessoa";

        $config = NULL;

        if ($type === "pessoa")
        {

            $this -> contjs['fields']['personal'] = array_merge($this -> contjs['fields']['personal'], $this -> contjs['fields']['comuns']);

            unset($this -> contjs['fields']['comuns']);
            unset($this -> contjs['fields']['company']);

        }
        else
        {

            $this -> contjs['fields']['company'] = array_merge($this -> contjs['fields']['company'], $this -> contjs['fields']['comuns']);

            unset($this -> contjs['fields']['prof']);
            unset($this -> contjs['fields']['comuns']);
            unset($this -> contjs['fields']['personal']);
        }

        $config = $this -> contjs;

        return $config;
    }

    /**
     * cria lista de relacionamentos
     *
     * @return obejct json
     */
    public function relations()
    {

        $js = $this -> contjs;

        $rslt = logex::$tcnx -> query("SELECT DISTINCT " . $js['admin']['public']['relation']['db'] . " FROM " . $js['table']);

        while ($rel = $rslt -> fetch_array())
        {

            if ($rel[0])
                $relation[] = '"' . $rel[0] . '"';
        }

        return '{"options":[' . implode(",", $relation) . ']}';
    }

    /**
     *
     * Envia os dados de contacto para a ficha da empresa.
     * Este metodo é solicitado quando o usuário clica no nome de um dos contatos ligados à empresa.
     *
     * @param $id_contact - id do contato
     *
     * @return string - objeto json com dados do contato
     *
     */
    public function contact_data($id_contact)
    {

        if (!parent::check())
            return FALSE;

        if (!$id = $this -> id($id_contact))
            return FALSE;

        if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            return FALSE;

        $query = NULL;
        $ret = NULL;

        unset($this -> contjs['fields']['comuns']['profile']);
        unset($this -> contjs['fields']['comuns']['tags']);
        unset($this -> contjs['fields']['comuns']['nif']);
        unset($this -> contjs['fields']['comuns']['country']);
        unset($this -> contjs['fields']['comuns']['state']);
        unset($this -> contjs['fields']['comuns']['city']);
        unset($this -> contjs['fields']['comuns']['village']);
        unset($this -> contjs['fields']['comuns']['postal']);
        unset($this -> contjs['fields']['comuns']['address']);

        foreach ($this -> contjs['fields']['comuns'] as $value)
        {

            if (isset($value['db']))
                $query .= $value['db'] . ",";
        }

        $rslt = logex::$tcnx -> query("SELECT  " . rtrim($query, ",") . "  FROM " . $this -> table . " WHERE " . $this -> idb . "=$id LIMIT 1");

        foreach ($rslt->fetch_array(MYSQLI_NUM) as $valuex)
        {

            if ($valuex)
            {

                $ret .= '"' . $valuex . '",';
            }
        }

        return '{"result":[' . rtrim($ret, ",") . ']}';

    }

    /**
     * Guarda um contacto na base de dados
     *
     * @param string $c_type - tipo de contato (pessoa ou  empresa)
     * @param string $c_type - relacionamento
     *
     * @return string - com sucesso um objeto json ou alerta em caso de falha
     *
     */
    public function insert_contact($mode, $c_relation)
    {

        if (!parent::check())
            return parent::mess_alert("CON" . __LINE__);

        if (!isset($_POST))
            return parent::mess_alert("CON" . __LINE__);

        $save_mode = ($mode == "ADD" || $mode == "UPDATE") ? $mode : FALSE;

        if (!$save_mode)
            return parent::mess_alert("CON" . __LINE__);

        $_POST['public_folder'] = (isset($_POST['public_folder'])) ? parent::validate_name($_POST['public_folder']) : NULL;

        $id = parent::id($_POST['toke']);

        //define as configurações do contato de acordo com a categoria
        $contact_type = (strtolower($_POST['public_category']) === "empresa") ? "empresa" : "pessoa";
        $cont_config = $this -> configure_operations_sheet($contact_type);

        //grava o resultado na base de dados
        $contact_saver = new OperationsBar($cont_config);

        if (!$id && $save_mode == "ADD")
        {

            $id = $this -> save_add_contact($contact_saver -> save_add(), $c_relation);
        }

        if ($save_mode == "UPDATE")
        {

            if (!$id = parent::id($_POST['toke']))
                return parent::mess_alert("CON" . __LINE__);

            $id = $this -> save_edit_contact($contact_saver -> save_edit($id), $id, $c_relation);
        }

        $f_id = parent::id($id);

        return '{"result":["' . $_POST['public_folder'] . '","i:' . $f_id . '","' . $c_relation . '","' . $contact_type . '"]}';

    }

    private function save_add_contact($query, $c_relation)
    {

        $emp = new Employees();

        $tc = new mysqli('localhost', _US, _PS, _DB);
        $tc -> set_charset("utf8");

        $tc -> autocommit(FALSE);

        if (!$tc -> query($query))
        {

            $tc -> rollback();
            return parent::mess_alert("CON" . __LINE__);
        }

        $id = $tc -> insert_id;

        if ($c_relation == "Colaborador")
        {

            if (!$tc -> query("INSERT INTO " . $emp -> get_emp_table() . " SET " . $emp -> get_emp_dbtoke() . "=" . $id))
            {

                $tc -> rollback();

                return parent::mess_alert("CON" . __LINE__);
            }
        }

        $tc -> commit();

        $tc -> close();

        return $id;

    }

    private function save_edit_contact($query, $c_id, $c_relation)
    {

        if (!$id = parent::id($c_id))
            return parent::mess_alert("CON" . __LINE__);

        $emp = new Employees();

        //verifica se o contacto deixa de ser Colaborador
        $rel = $this -> query_contact($id);
        $ex_emp = ($rel && ($rel[$this -> contjs['admin']['public']['relation']['db']] == "Colaborador") && ($c_relation != "Colaborador")) ? TRUE : FALSE;

        //retira contacto da categoria de colaborador
        if ($ex_emp)
        {

            $save = $emp -> delete_employee($id);

            $retur = json_decode($save, TRUE);
            if (!$retur)
                return parent::mess_alert("CON" . __LINE__);

            if (isset($retur['alert']))
                return $save;

        }
        else
        {

            $tc = new mysqli('localhost', _US, _PS, _DB);
            $tc -> set_charset("utf8");

            $tc -> autocommit(FALSE);

            if (!$tc -> query($query))
            {

                $tc -> rollback();
                return parent::mess_alert("CON" . __LINE__);
            }

            if ($c_relation == "Colaborador")
            {

                if (!$tc -> query("INSERT IGNORE INTO " . $emp -> get_emp_table() . " SET " . $emp -> get_emp_dbtoke() . "=" . $id))
                {

                    $tc -> rollback();
                    return parent::mess_alert("CON" . __LINE__);
                }
            }

            $tc -> commit();
            $tc -> close();
        }

        return $id;
    }

    /**
     * Apaga um contacto da base de dados
     *
     * @param string $id_contact - id do contato
     *
     * @return boolean | object json
     *
     */
    public function delete_contact($id_contact)
    {

        if (!parent::check())
            return parent::mess_alert("CON" . __LINE__);

        //apaga nas tabelas dos colaboradores (verificação para eliminar erros que possam existir na tabela)
        $del_emp = new Employees();
        $delet_emp = $del_emp -> delete_employee($id_contact);

        //apaga o contacto
        $del_contact = new OperationsBar($this -> contjs);
        $delet = $del_contact -> delete_item($id_contact);

        if (!$delet)
            return parent::mess_alert("CON" . __LINE__);

        return $delet;

    }

    /**
     * Cria a ficha de apresentação de um contato
     *
     * @param string $id_contact - id do contato
     *
     * @return string - estrutura html com a ficha de apresentação
     *
     */
    public function make_file($id_contact)
    {

        if (!parent::check())
            return parent::mess_alert("CON" . __LINE__);

        if (!$contact_data = $this -> query_contact($id_contact))
            return parent::mess_alert("CON" . __LINE__);

        $contact_id_db = $contact_data[$this -> contjs['admin']['private']['toke']['db']];

        #VERIFICA A CATEGORIA
        $category = FALSE;

        if (isset($contact_data[$this -> typedb]))
            $category = strtolower($contact_data[$this -> typedb]);

        if (!$category)
            return parent::mess_alert("CON" . __LINE__);

        $send_news_db = $this -> contjs['admin']['public']['send_news']['db'];

        $contact_data[$send_news_db] = ($contact_data[$send_news_db]) ? "Sim" : "Não";

        $sheet = new ItemSheet;

        if ($category === "pessoa")
            return $sheet -> make_sheet($this -> contjs, $contact_data, $this -> make_file_person($contact_data, $sheet));

        if ($category == "empresa")
            return $sheet -> make_sheet($this -> contjs, $contact_data, $this -> make_file_company($contact_data, $contact_id_db, $sheet));

        return parent::mess_alert("CON" . __LINE__);
    }

    /**
     * Cria o conteúdo da ficha de apresentação de uma pessoa
     *
     * @param array $db_result - resultado da pesquisa na base de dados
     * @param ItemSheet $ISheet - instancia de ItemSheet
     *
     * @return string - estrutura html com o conteúdo da ficha
     *
     */
    protected function make_file_person(array $db_result, ItemSheet $ISheet)
    {

        $profi = NULL;
        $pers = NULL;

        $personal = &$this -> contjs['fields']['personal'];
        $company = &$this -> contjs['fields']['company'];
        $prof = &$this -> contjs['fields']['prof'];

        //manipulação do nome do contato para o nome completo aperecer em apenas um campo largo
        $db_result[$personal['title']['db']] = $db_result[$personal['title']['db']] . " " . $db_result[$personal['surname']['db']];
        $personal['title']['type'] = "L_INPUT";
        unset($personal['surname']);

        $person['personal'] = $personal;
        $person['comuns'] = $this -> contjs['fields']['comuns'];

        $pers = $ISheet -> make_sheet_content($person, $db_result);

        //Se estiver definida uma empresa para o contato cria a parte com os dados da empresa e os dados profissionais
        if ($db_result[$prof['company']['db']])
        {

            $company_data = $this -> query_contact($db_result[$prof['company']['db']]);

            //manipula o resultado da pesquisa da empresa da base de dados para atribuir os dados profissinais da pesquisa da pessoa
            $company_data[$prof['company']['db']] = $company_data[$company['title']['db']];
            $company_data[$prof['department']['db']] = $db_result[$prof['department']['db']];
            $company_data[$prof['job']['db']] = $db_result[$prof['job']['db']];

            //o nome da empresa é definido nos dados profissionais para ser o primeiro campo a ser apresentado. Apagamos o da company
            unset($company['title']);

            $pro['profi'] = $prof;
            $pro['company'] = $company;
            $pro['comuns'] = $this -> contjs['fields']['comuns'];

            $profi = $ISheet -> make_sheet_content($pro, $company_data);

        }

        return $pers . $profi;

    }

    /**
     * Cria o conteúdo da ficha de apresentação de uma empresa
     *
     * @param array $db_result - resultado da pesquisa na base de dados
     * @param string $id_company - id da empresa
     * @param ItemSheet $ISheet - instancia de ItemSheet
     *
     * @return string - estrutura html com o conteúdo da ficha
     *
     */
    protected function make_file_company($db_result, $id_company, $ISheet)
    {

        $company = $ISheet -> make_sheet_content(array(
            "company" => $this -> contjs['fields']['company'],
            "comuns" => $this -> contjs['fields']['comuns']
        ), $db_result);

        return $company . $this -> employees_data($id_company);

    }

    /**
     * Cria uma listagem com os nomes dos pessoas associadas a uma empresa.
     * As linhas desta tabela tem o atributo data-action = contact
     *
     * @param string $idx - id da empresa
     *
     * @return string - estrutura html com um tabela com os nomes das pessoas
     *
     */
    private function employees_data($id_company)
    {

        if (!parent::check())
            return parent::mess_alert("CON" . __LINE__);

        $id = $this -> id($id_company);

        if (!$id)
            return parent::mess_alert("CON" . __LINE__);

        $employee = NULL;

        extract($this -> contjs['admin']['private'], EXTR_PREFIX_ALL, "adm");

        $company = $this -> contjs['fields']['prof']['company']['db'];
        $job = $this -> contjs['fields']['prof']['job']['db'];
        $admidentifier = $adm_identifier['db'];

        $rslt = logex::$tcnx -> query("SELECT $admidentifier,$job,$adm_toke[db] FROM " . $this -> contjs['table'] . " WHERE $company=$id");

        while ($emp = $rslt -> fetch_array())
        {

            $employee .= "
                    <tr data-id='" . $emp[$adm_toke['db']] . "' data-action='contact'>
                    <td>
                    " . parent::make_title($adm_identifier, $emp) . "
                    </td>
                    <td>
                    " . $emp[$job] . "
                    </td>
                    </tr>";
        }

        mysqli_free_result($rslt);

        return "<div class='wlang'><div class='filehalfdiv'><table>$employee</table></div><div class='filehalfdiv' id='infoCont'></div></div>";

    }

    /**
     * envia opções de paises, distritos, cidade e freguesias
     */
    public function address_options()
    {
        if (parent::check())
        {

            if (!in_array($_POST['flag'], $GLOBALS['FLAGSMODE']))
            {
                return '{"alert":"Não foi possivel realizar esta operação. - CON' . __LINE__ . '"}';
            }

            $op["state"] = array("pais" => "distrito");
            $op["city"] = array("distrito" => "cidade");
            $op["village"] = array("cidade" => "freguesia");

            echo $this -> optionsDis("contactos", $op);
        }
    }

    public function show_notes()
    {
        if ($this -> check())
        {

            $id = $this -> id($_POST['toke']);

            if ($id)
                extract($this -> contjs['fields']['manag']);

            return $this -> do_notes('notas', $this -> contjs['table'], $toke['db'], $id);
        }
    }

}

/**
 *
 */
class ContGestFolder extends GestFolders
{

    private $condition = NULL;

    function __construct($argument, $category = NULL, $relation = NULL)
    {

        parent::__construct($argument);

        $relation = $this -> verificaNome($relation);

        if ($category === 'pessoas')
        {
            $this -> condition = " where categoria='pessoa' AND relacionamento ='$relation'";
        }
        elseif ($category === 'empresas')
        {
            $this -> condition = " where categoria='empresa' AND relacionamento ='$relation'";
        }
        else
        {
            $this -> condition = " where relacionamento ='$relation'";
        }

        parent::set_folder_query("SELECT DISTINCT pasta FROM contactos $this->condition");
    }

    /*
     * Subescreve a função da classe mãe
     *
     */
    protected function set_item_query($folder)
    {

        $item_query = "select nome, apelido, mail, id from contactos $this->condition AND pasta='$folder'";

        $q_item = logex::$tcnx -> query($item_query);

        return $this -> get_folders_itens($q_item, $folder);
    }

}

/**
 *
 */
class Employees extends Contacts
{

    private $emp_config;
    private $table;
    public $cont_config;

    #coluna chave primaria na base de dados
    private $dbtoke;

    public function __construct()
    {

        parent::__construct();

        $this -> emp_config = $this -> json_file("JEMPLOYEES");

        $this -> table = $this -> emp_config['table'];

        $this -> dbtoke = $this -> emp_config['admin']['private']['toke']['db'];

        $this -> cont_config = $this -> get_contact_config();

    }

    public function get_emp_table()
    {

        return $this -> table;
    }

    public function get_emp_dbtoke()
    {

        return $this -> dbtoke;
    }

    /**
     *
     */
    public function get_emp_config()
    {
        return $this -> emp_config;
    }

    /**
     * Procura um colaborador na base de dados
     *
     * @param $idx - id do colaborador a procuras
     *
     * @return array - resultado da pesquisa na base de dados
     *
     */
    protected function query_employee($idx)
    {

        $id = (parent::id($idx));

        if (!$id)
            return FALSE;

        $emp_id_db = $this -> dbtoke;
        $cont_id_db = $this -> cont_config['admin']['private']['toke']['db'];

        $query = "SELECT * FROM 
                  contactos
                  INNER JOIN colaboradores
                  ON colaboradores.id_contacto = contactos.id 
                  LEFT OUTER JOIN access_col 
                  ON access_col.id_colb = colaboradores.id_contacto
                  WHERE colaboradores.id_contacto = $id 
                  LIMIT 1";

        $result = logex::$tcnx -> query($query);

        $employee = $result -> fetch_assoc();

        mysqli_free_result($result);

        return $employee;

    }

    /**
     * Cria a ficha de apresentação de um colaborador
     *
     * @param string $id_emp - id do colaborador
     *
     * @return string - estrutura html com a ficha de apresentação
     *
     */
    public function make_emp_sheet($id_emp)
    {

        if (!parent::check())
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        $employee_data = $this -> query_employee($id_emp);

        if (!$employee_data)
            return NULL;

        #Define o estado do contato
        $status_db = $this -> emp_config['admin']['private']['status']['db'];
        $this -> cont_config['admin']['private']['status']['db'] = $status_db;

        #VERIFICA A CATEGORIA
        $category = FALSE;

        if (isset($employee_data[$this -> cont_config['admin']['public']['category']['db']]))
            $category = strtolower($employee_data[$this -> cont_config['admin']['public']['category']['db']]);

        if (!$category)
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        $send_news_db = $this -> cont_config['admin']['public']['send_news']['db'];

        $employee_data[$send_news_db] = ($employee_data[$send_news_db]) ? "Sim" : "Não";

        $ISheet = new ItemSheet();

        if (isset($this -> emp_config['fields']['pt']))
            unset($this -> emp_config['fields']['access']);
        $module_data = $ISheet -> make_sheet_content($this -> emp_config['fields'], $employee_data);

        $person_content = $module_data . $this -> make_file_person($employee_data, $ISheet);

        $company_content = $module_data . $this -> make_file_company($employee_data, $id_emp, $ISheet);

        if ($category === "pessoa")
            return $ISheet -> make_sheet($this -> cont_config, $employee_data, $person_content);

        if ($category == "empresa")
            return $ISheet -> make_sheet($this -> cont_config, $employee_data, $company_content);

        return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

    }

    /**
     * Cria ficha para adição de um novo contato
     *
     * @return string - estrutura html com a ficha
     *
     */
    public function add_employee()
    {

        if (!parent::check())
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        $contact_type = (strtolower($_POST['type']) === "empresa") ? "empresa" : "pessoa";

        $this -> configure_operations_sheet($contact_type);

        $cont_config = $this -> get_contact_config();

        $predef[$this -> cont_config['admin']['public']['category']['db']] = $contact_type;
        $predef[$this -> cont_config['admin']['public']['relation']['db']] = "Colaborador";

        $cont_config['admin']['private']['status'] = $this -> emp_config['admin']['private']['status'];
        $cont_config['fields']['access'] = $this -> emp_config['fields']['access'];

        $datalist = New ElementDatalist();

        if (isset($this -> emp_config['fields']['pt']))
            $datalist -> make_datalist_options($this -> emp_config['fields']['pt'], $this -> emp_config['table'], $employee_data);
        $cont_config['fields']['pt'] = $this -> emp_config['fields']['pt'];

        if (isset($this -> emp_config['fields']['en']))
            $datalist -> make_datalist_options($this -> emp_config['fields']['en'], $this -> emp_config['table'], $employee_data);
        $cont_config['fields']['en'] = $this -> emp_config['fields']['en'];

        unset($datalist);

        $add_sheet = new OperationsBar($cont_config);

        return $add_sheet -> add_item_sheet($predef);

    }

    /**
     * Cria ficha para a edição dos dodos de um colaborador
     *
     * @param string $id_emp - id do colaborador
     *
     * @return string - estrutura html com a ficha de edição
     *
     */
    public function edit_employee($id_emp)
    {

        if (!parent::check())
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        $employee_data = $this -> query_employee($id_emp);

        if (!$employee_data)
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        $cont_config = $this -> get_contact_config();

        #VERIFICA A CATEGORIA
        $contact_type = FALSE;

        if (isset($employee_data[$cont_config['admin']['public']['category']['db']]))
            $contact_type = strtolower($employee_data[$cont_config['admin']['public']['category']['db']]);

        if (!$contact_type)
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        $this -> configure_operations_sheet($contact_type);

        $cont_config = $this -> get_contact_config();

        $cont_config['admin']['private']['status'] = $this -> emp_config['admin']['private']['status'];

        $cont_config['fields']['access'] = $this -> emp_config['fields']['access'];

        $datalist = New ElementDatalist();

        if (isset($this -> emp_config['fields']['pt']))
            $datalist -> make_datalist_options($this -> emp_config['fields']['pt'], $this -> emp_config['table'], $employee_data);
        $cont_config['fields']['pt'] = $this -> emp_config['fields']['pt'];

        if (isset($this -> emp_config['fields']['en']))
            $datalist -> make_datalist_options($this -> emp_config['fields']['en'], $this -> emp_config['table'], $employee_data);
        $cont_config['fields']['en'] = $this -> emp_config['fields']['en'];

        unset($datalist);

        $add_sheet = new OperationsBar($cont_config);

        return $add_sheet -> edit_item_sheet($employee_data);
    }

    /**
     * Cria as pastas dos colaboradores
     *
     * @return string - objeto json com uma array de objetos {"id":"","status":"","name":"","image":""}
     *
     */
    public function employees_folder()
    {

        if (!parent::check())
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        $this -> cont_config['admin']['private']['status']['db'] = "estado";

        $folder = new EmpGestFolder($this -> cont_config);

        $folder -> set_folder_query("select contactos.pasta from contactos inner join colaboradores on colaboradores.id_contacto=contactos.id");

        return $folder -> make_folders();
    }

    /**
     * Inicia a gravação de um contato na base de dados
     *
     * @return string - em caso de sucesso json com os dados, em caso de falha json com mensagem de erro
     *
     */
    public function insert_employee()
    {

        if (!parent::check())
            return '{"alert":"Não tem permissão para realizar esta operação. EMP' . __LINE__ . '"}';

        if (!isset($_POST))
            return '{"alert":"Não tem permissão para realizar esta operação. EMP' . __LINE__ . '"}';

        $save_mode = ($_POST['filemode'] == "UPDATE" || $_POST['filemode'] == "ADD") ? $_POST['filemode'] : FALSE;

        if (!$save_mode)
            return '{"alert":"Não tem permissão para realizar esta operação. EMP' . __LINE__ . '"}';

        if ($_POST["public_relation"] != "Colaborador" && $save_mode == "ADD")
            return '{"alert":"Está a tentar salvar um contato que tem um RELACIONAMENTO diferente de COLABORADOR. \\n\\n Por favor, utilize o modulo CONTACTOS para realizar esta operação."}';

        $identy = (isset($_POST['toke']) && parent::id($_POST['toke'])) ? $_POST['toke'] : FALSE;

        $sts = (isset($_POST['public_status']) && $_POST['public_status'] === "online") ? TRUE : FALSE;

        $_POST['public_folder'] = (isset($_POST['public_folder'])) ? parent::validate_name($_POST['public_folder']) : NULL;

        $query = NULL;

        if ($save_mode == "ADD" && !$identy)
        {

            $query = $this -> make_query("ADD");
        }

        if ($save_mode == "UPDATE" && $identy)
        {

            if ($_POST["public_relation"] != "Colaborador")
                return $this -> delete_employee($identy);

            $query = $this -> make_query("UPDATE");
        }

        if (!is_array($query))
            return $query;

        return $this -> save_employee($query);

    }

    private function make_query($operation)
    {

        if ($clean_input = $this -> validate_access())
            return $clean_input;

        #VERIFICA A CATEGORIA
        $contact_type = FALSE;

        if (isset($_POST['public_category']))
            $contact_type = strtolower($_POST['public_category']);

        if (!$contact_type)
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        #CONTACTO
        $this -> configure_operations_sheet($contact_type);
        $cont_config = $this -> get_contact_config();
        $contact_saver = new OperationsBar($cont_config);

        #COLABORADOR
        $contact = $this -> emp_config['admin']['private']['toke']['db'];
        unset($this -> emp_config['fields']['access']['pass']);
        unset($this -> emp_config['fields']['access']['nick']);
        $emp_saver = new OperationsBar($this -> emp_config);

        #ADICIONAR
        if ($operation === "ADD")
        {

            $query['contact'] = $contact_saver -> save_add();
            $query['employee'] = $emp_saver -> save_add() . ',' . $contact . '=$emp_id';
            $query['id'] = NULL;
        }

        #ATUALIZAR
        if ($operation === "UPDATE")
        {

            $query['contact'] = $contact_saver -> save_edit($_POST['toke']);
            $query['employee'] = $emp_saver -> save_edit($_POST['toke']);
            $query['id'] = $_POST['toke'];
        }

        return $query;

    }

    /**
     * Grava os dados do colabordor na base de dados
     *
     * @param array $query - array com 3 elementos :
     *                              [contact] (query para salvar na tabela contactos),
     *                              [employee] (query para gravar na tabela colaboradores),
     *                              [id] (id do contato que irá ser atualizado)
     *
     * @return string - em caso de sucesso json com os dados, em caso de falha json com mensagem de erro
     *
     */
    private function save_employee(array $query)
    {

        $tc = new mysqli('localhost', _US, _PS, _DB);
        $tc -> set_charset("utf8");

        $tc -> autocommit(FALSE);

        #INSERE CONTACTO
        if (!$tc -> query($query['contact']))
        {
            $tc -> rollback();
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';
        }

        if (!($emp_id = $tc -> insert_id) && !($emp_id = $query['id']))
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';

        eval('$e="' . $query['employee'] . '";');

        if (!$tc -> query($e))
        {
            $tc -> rollback();
            return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';
        }

        #INSERE ACESSO
        if (!empty($_POST['access_nick']) && !empty($_POST['access_pass']))
        {

            $access_query = "
                INSERT INTO access_col  
                (id_colb, senha, nick) VALUES ($emp_id, '$_POST[access_pass]', '$_POST[access_nick]')  
                ON DUPLICATE KEY UPDATE senha='$_POST[access_pass]', nick='$_POST[access_nick]'
                ";

            if (!$tc -> query($access_query))
            {

                $erro = $tc -> error;
                $tc -> rollback();
                if ($erro === 1062)
                    return '{"alert":"O conjunto nick/senha já existe."}';

                return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';
            }

        }
        else
        {

            if (!$tc -> query("DELETE FROM access_col WHERE id_colb=$emp_id"))
            {

                $tc -> rollback();

                return '{"alert":"Não foi possivel realizar esta operação. EMP' . __LINE__ . '"}';
            }
        }

        $tc -> commit();

        return '{"result":["' . parent::validate_name($_POST['public_folder']) . '","' . $emp_id . '"]}';
    }

    /**
     *
     */
    public function delete_employee($id_employee)
    {

        if (!parent::check())
            return parent::mess_alert("EMP" . __LINE__);

        if (!$emp_id = parent::id($id_employee))
            return parent::mess_alert("EMP" . __LINE__);

        $tc = new mysqli('localhost', _US, _PS, _DB);
        $tc -> set_charset("utf8");

        if (mysqli_connect_errno())
            return parent::mess_alert("EMP" . __LINE__);

        $tc -> autocommit(FALSE);

        #ACESSO
        if (!$tc -> query("DELETE FROM access_col WHERE id_colb=$emp_id"))
        {

            $tc -> rollback();
            return parent::mess_alert("EMP" . __LINE__);
        }

        #COLABORADORES
        $contact = $this -> emp_config['admin']['private']['toke']['db'];
        if (!$tc -> query("DELETE FROM $this->table WHERE $contact=$emp_id"))
        {

            $tc -> rollback();
            return parent::mess_alert("EMP" . __LINE__);
        }

        #ATUALIZA CONTATOS
        $folder = $this -> cont_config['admin']['private']['folder']['db'];
        $relation = $this -> cont_config['admin']['public']['relation']['db'];
        $toke = $this -> cont_config['admin']['private']['toke']['db'];
        $table = $this -> cont_config['table'];

        //Quando a categoria muda de colaborador para outra
        if (!empty($_POST['public_category']) && ($_POST['filemode'] == "UPDATE"))
        {

            $query = $this -> make_query("UPDATE");

            if (!isset($query['contact']))
                return $query;

            if (!$tc -> query($query['contact']))
            {
                $tc -> rollback();
                return parent::mess_alert("EMP" . __LINE__);
            }

        }
        //quando o contacto foi apagado pelo barra de operações
        else
        {

            if (!$rfold = $tc -> query("UPDATE $table SET $relation = 'Ex-colaborador' WHERE $toke = $emp_id"))
            {

                $tc -> rollback();
                return parent::mess_alert("EMP" . __LINE__);
            }
        }

        $folder = $this -> cont_config['admin']['private']['folder']['db'];
        $toke = $this -> cont_config['admin']['private']['toke']['db'];
        $table = $this -> cont_config['table'];

        if (!$rfold = $tc -> query("SELECT $folder FROM $table WHERE $toke = $emp_id"))
        {

            $tc -> rollback();
            return parent::mess_alert("EMP" . __LINE__);
        }

        $folders = $rfold -> fetch_array();

        $tc -> commit();

        return '{"result":["' . $folders[0] . '","' . $emp_id . '"]}';

    }

    /**
     * Sanitiza os inputs da tabela de colaboradores
     *
     * @return boolean|string - False se não encotra erros ou mensagem de erros
     */
    private function validate_access($id_contact = "''")
    {

        $vlevel = (isset($_POST['access_level'])) ? filter_var($_POST['access_level'], FILTER_VALIDATE_INT) : "''";
        $_POST['public_status'] = (isset($_POST['public_status']) && $_POST['public_status'] === "online") ? "online" : "offline";

        if ($vlevel < 0 || $vlevel > 4)
            return '{"alert":"Não foi possivel guardar os dados de acesso. EMP' . __LINE__ . '"}';

        if (!empty($_POST['access_nick']) && !empty($_POST['access_pass']))
        {

            $_POST['access_nick'] = ($this -> validate_name($_POST['access_nick'])) ? $this -> validate_name($_POST['access_nick']) : NULL;
            $_POST['access_pass'] = ($this -> validate_name($_POST['access_pass'])) ? $this -> validate_name($_POST['access_pass']) : NULL;

            if (empty($_POST['access_pass']))
                return '{"alert":"Senha inválida."}';

            if (empty($_POST['access_nick']))
                return '{"alert":"Nick inválido."}';

            $r_pass = logex::$tcnx -> query("SELECT * FROM access_col WHERE senha='$_POST[access_pass]' AND nick <> '$_POST[access_nick]'");

            if ($r_pass -> num_rows > 0)
                return '{"alert":"Senha repetida."}';

        }

        return FALSE;
    }

    /**
     * Cria objecto para ordenação
     *
     * @return object json
     *
     */
    public function order()
    {
        if (!parent::check())
            return parent::mess_alert("EMP" . __LINE__);

        $contact_table = parent::get_table();
        $emp_table = $this -> table;

        $query = "
                SELECT id,imagem, nome, apelido,categoria, order_index FROM $contact_table 
                INNER JOIN $emp_table 
                ON $contact_table.id = $emp_table.id_contacto
                ORDER BY order_index DESC
            ";

        if (!$q = logex::$tcnx -> query($query))
            return parent::mess_alert("EMP" . __LINE__);

        $or = NULL;
        while ($od = $q -> fetch_array())
        {

            if (!$od[1])
                $ig = ($od[4] === "Empresa") ? "../imagens/ftEmp.png" : "../imagens/ftCont.png";

            $images = new GestImage();

            $img = ($od[1]) ? '"' . $images -> send_images_json($od[1], "src", NULL, 0, NULL) . '"' : '"' . $ig . '"';
            $or .= ",[\"$od[0]\",$img,\"$od[2]\"]";
        }

        return '{"result":[' . ltrim($or, ",") . ']}';
    }

}

/**
 *
 */
class EmpGestFolder extends GestFolders
{

    function __construct($argument)
    {

        parent::__construct($argument);
    }

    /*
     * Subescreve a função da classe mãe
     *
     */
    protected function set_item_query($folder)
    {

        $item_query = "select colaboradores.estado, contactos.nome, contactos.apelido, contactos.mail, contactos.id from colaboradores inner join contactos on colaboradores.id_contacto=contactos.id WHERE contactos.pasta='$folder'";

        $q_item = logex::$tcnx -> query($item_query);

        return $this -> get_folders_itens($q_item, $folder);
    }

}
?>
