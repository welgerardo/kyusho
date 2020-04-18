<?php

/**
 * script: Contacts.php
 * client: EPKyusho
 *
 * @version V1.50.100615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */
//FIXME - verificar todas as funções para apagar contacto e otimizar se poder utilizar a função delete da class operationsbar
ini_set('display_errors', 0);
require_once 'Core.php';

/**
 * Esta classe faz a gestão dos contactos.
 * 
 * Stored procedures necessárias:
 * - spContactsGroup *
 * - spContactsFolders *
 * - spChangeContactsFolders *
 * - spInsertContact *
 * - spUpdateContact *
 * - spContactsModules *
 * - spContactsRelations *
 * - spDeleteContact *
 * - spContactData *
 * - spEmpresaPessoa *
 * - spContactRelations *
 * 
 * Classe filhas
 * - Employees
 * - Clients
 * - ContactsGroup 
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version V1.50.100615
 * @since 10/06/2015
 * @license Todos os direitos reservados
 */
class Contacts extends Core
{

    /**
     * Configuração do modulo
     * @var array
     */
    private $contjs;

    /**
     * Nome da tabela do modulo 
     * @var string
     */
    private $table;

    /**
     * Nome da idetificador único de item do modulo
     * @var string
     */
    private $idb;

    /**
     * Nome da columa que guarda o tipo de contacto (Pessoa|Empresa)
     * @var string
     */
    private $typedb;

    /**
     * Nome da coluna que define o grupo
     * 
     * @var string
     */
    private $reldb;

    /**
     * Código de erro da classe 
     * 
     * @var string
     */
    private $exp_prefix = "CONT";

    /**
     *
     */
    public function __construct()
    {

        parent::__construct();

        $this->contjs = $this->json_file("JCONTACTS");

        $this->table = $this->contjs['table'];

        $this->idb = $this->contjs['admin']['private']['toke'];

        $this->typedb = $this->contjs['admin']['public']['category']['db'];

        $this->reldb = $this->contjs['admin']['public']['group']['db'];
    }

    /**
     * Retorna a array de configuração dos contatos
     *
     * @return array associativa
     *
     */
    public function get_contact_config()
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return $this->contjs;
    }

    /**
     * Retorna o nome da tabela do modulo
     *
     * @return string
     *
     */
    public function get_table()
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return $this->table;
    }

    /**
     * Retorna o nome da tabela onde são guardados as notas do modulo
     *
     * @return string
     *
     */
    public function get_notes_table()
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return $this->contjs['notes'];
    }

    /**
     * !obsoleta ????
     * FIXME verificar se é usada
     *
     * Retorna uma array com o nome das columas que guardam:
     * folder = pastas
     * group = grupo
     * toke = identificação unica
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     *
     * @return array
     */
    public function get_cols_name()
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $data['folder'] = parent::db_cols($this->contjs['admin']['public']['folder']['db']);
        $data['group']  = parent::db_cols($this->contjs['admin']['public']['group']['db']);
        $data['toke']   = parent::db_cols($this->contjs['admin']['private']['toke']);

        return $data;
    }

    /**
     * Procura na base de dados um grupo de contatos que permitem receber a newsletter, tem email definido e aceitam receber a newsletter.
     * Retorna o id e o email pela ordem : id, mail
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core::make_call()
     *
     * @throws Exception
     *
     *
     * @param string|array $contacts - string do inteiros separados por virgulas ou um array com valores inteiros que sejam o valor da chave primária
     *
     * @return array
     *
     */
    public function query_contacts_group($contacts)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #VALIDA OS CONTATOS
        $conts = (is_array($contacts)) ? $contacts : explode(",", $contacts);

        if (!is_array($conts))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        $uconts = array_unique($conts);

        $group_members = implode(",", array_filter($uconts, "Core::validate_int"));

        try
        {
            return parent::make_call("spContactsGroup", array($group_members));
        }
        catch (Exception $exp)
        {

            throw new Exception($exp->getMessage());
        }
    }

    /**
     * Lista de pastas e contactos
     *
     * @uses Core::check()
     * @uses Core::mess_alert()
     * @uses Anti::verificaNome()
     * @uses GestFolders::make_folders
     *
     * @param string $category - catgoria do contato pessoa ou empresa
     * @param string $ngroup - grupo a que o contato pertence
     *
     * @return json com contatos ou mensagem de erro
     */
    public function make_contacts_folders($category = "", $ngroup = "")
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $group = parent::verificaNome($ngroup);

        if (!$group)
            $group = "";

        $cate = (strtolower($category) === "pessoas" || strtolower($category) === "empresas") ? strtolower($category) : "";

        $folder = new GestFolders();
        return $folder->make_folders("spContactsFolders", array($group, $cate));
    }

    /**
     * Muda um contato para outra pasta
     *
     * @uses Core::check()
     * @uses Core::mess_alert()
     * @uses Core::id()
     * @uses Anti::validate_name()
     * @uses Anti::verificaNome()
     * @uses GestFolders::change_folder()
     *
     * @param string $toke - identificador unico do contato
     * @param string $new_folder - nome da nova pasta
     * @param string $category - categoria do contacto (Pessoa ou empresa)
     * @param string $group - nome do grupo a que o contato pretence
     *
     * @return json
     */
    public function change_contacts_folders($toke, $new_folder, $category = NULL, $group = NULL)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $id = $this->id($toke);

        if (!$id)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $n_folder = parent::validate_name($new_folder);

        if (!$group)
            $group = "";

        $cate = (strtolower($category) === "pessoas" || strtolower($category) === "empresas") ? strtolower($category) : "";

        $folder = new GestFolders();
        return $folder->change_folder("spChangeContactsFolders", array($id, $n_folder, $group, $cate));
    }

    /**
     * Cria a ficha de apresentação de um contato
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses ItemSheet::make_sheet()
     * @uses Contacts::query_full_contact()
     * @uses Contacts::prep_file()
     *
     * @param string $id_contact - id do contato
     *
     * @return string - estrutura html com a ficha de apresentação
     *
     */
    public function make_file($id_contact)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $contact_data = $this->query_full_contact($id_contact, "SHEET");

            $sheet = new ItemSheet();

            $content = $this->prep_file($contact_data, $sheet);

            return $sheet->make_sheet($this->contjs, $contact_data, $content);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }
    }

    /**
     * Cria ficha para adição de um novo contato
     *
     * @uses Logex::check(), Contacts::configure_operations_sheet(), OperationsBar::add_item_sheet()
     *
     * @param string $c_type - tipo de contato (pessoa ou empresa)
     *
     * @return string - estrutura html com a ficha
     *
     */
    public function add_contact($c_type)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $type = ($c_type == "Empresa") ? "empresa" : "pessoa";

        #Configura conforme o tipo de contacto
        $contact = $this->configure_operations_sheet($type);

        $predef[$this->typedb] = $contact['type'];

        $add_sheet = new OperationsBar($contact['config']);
        $add_sheet->set_mode("ADD");

        return $add_sheet->add_item_sheet($predef);
    }

    /**
     * Cria ficha de edição do contacto
     *
     * @uses Logex::check(), Core::mess_alert(), Contacts::query_contact(), Contacts::configure_operations_sheet(), OperationsBar::edit_item_sheet()
     *
     * @param string $id_contact - id do contacto (chave primária)
     *
     * @return string - estrtura HTML com a ficha de edição de um contacto
     *
     */
    public function edit_contact($id_contact)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);
        try
        {
            #Procura os dados do contacto na base de dados
            $contact_data = $this->query_full_contact($id_contact);

            if (!isset($contact_data['contactos.categoria']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            #Configura conforme o tipo de contacto
            $contact = $this->configure_operations_sheet(strtolower($contact_data['contactos.categoria']));

            $edit_sheet = new OperationsBar($contact['config']);
            $edit_sheet->set_mode("UPDATE");

            return $edit_sheet->edit_item_sheet($contact_data);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }
    }

    /**
     * Guarda um contacto na base de dados
     *
     * @uses Logex::check(), Core::mess_alert(), Core::id(), Anti::validate_name, OperationsBar::save_add(), , OperationsBar::save_add(), Contacts::save_add_contact(), Contacts::save_edit_contact()
     *
     * @param string $mode - tipo de operação ADD = adição, UPDATE = atualização
     *
     * @return string - com sucesso um objeto json ou alerta em caso de falha
     *
     */
    public function insert_contact($mode)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!isset($_POST))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $save_mode = ($mode == "ADD" || $mode == "UPDATE") ? $mode : FALSE;

        if (!$save_mode)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $_POST['public_folder'] = (isset($_POST['public_folder'])) ? parent::validate_name($_POST['public_folder']) : NULL;

        $id = parent::id($_POST['toke']);

        #define as configurações do contato de acordo com a categoria
        $contact_type = (strtolower($_POST['public_category']) === "empresa") ? "empresa" : "pessoa";

        $cont_config = $this->configure_operations_sheet($contact_type);

        #verifica se os dados publicos das redes sociais do contato já foram apresentados para seleção
        if (!empty($_POST['jssocial']) && $_POST['jssocial'] == "y")
        {
            #se sim substitui os dados pelos das redes sociais
            $this->facebook_data();
        }
        else
        {
            #se não envia os dados para seleção e pára a gravação dos dados
            $sd = $this->social_data($_POST, $id);
            if ($sd)
                return $sd;
        }

        $contactos = NULL;
        $dados     = NULL;
        $ret       = NULL;

        $_POST['public_date_act'] = "";
        $_POST['public_date']     = "";

        if ($save_mode == "UPDATE" && $id)
            unset($_POST['public_date']);

        #constroi a frase para gravar na base de dados
        $contact_saver = new OperationsBar($cont_config['config']);
        $query         = $contact_saver->make_query();

        $modules = $this->query_modules();

        $relations = $this->query_relation();

        #confirma se recebeu os resultados esperados
        if (!(isset($query['contactos']) && (isset($query['dados_pessoa']) || isset($query['dados_empresa']))))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $contactos = $query['contactos'];
        $dados     = (isset($query['dados_pessoa'])) ? $query['dados_pessoa'] : $query['dados_empresa'];

        if (!$id && $save_mode == "ADD" && $contactos)
        {
            $procedure = "CALL spInsertContact(?,?,?,?)";
        }
        else if ($save_mode == "UPDATE" && $contactos)
        {
            if (!$id)
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $procedure = "CALL spUpdateContact(?,?,?,?,?)";
        } else
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        $rows = NULL;

        try
        {

            $dbcon = new PDO(_PDOM, _US, _PS);
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $dbcon->prepare($procedure);

            $stmt->bindValue(1, $contactos);
            $stmt->bindValue(2, $dados);
            $stmt->bindValue(3, $modules);
            $stmt->bindValue(4, $relations);

            if ($id)
                $stmt->bindValue(5, $id, PDO::PARAM_INT);

            $stmt->execute();

            try
            {
                $rows = $stmt->fetchAll();
            }
            catch (PDOException $ex)
            {
                $errtto = $ex->getMessage();
            }



            $stmt->closeCursor();
        }
        catch (PDOException $exp)
        {

            $err = $exp->getMessage();
        }

        $dbcon = NULL;

        $ret = json_decode($rows[0]['ret'], TRUE);

        if (!empty($ret))
        {
            if (isset($ret['mgp_error']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            if (!isset($ret['id']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $rid = parent::id($ret['id']);

            if (!$rid)
                return parent::mess_alert($this->exp_prefix . __LINE__);

            return '{"result":["' . $ret['pasta'] . '","i:' . $rid . '","' . $ret['rel'] . '","' . $ret['cat'] . '"]}';
        }
        else
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }
    }

    /**
     * envia as pastas dos contactos
     */
    public function make_contacts_modules()
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $folder = new GestFolders("MODULE");
        return $folder->make_folders("spContactsModules");
    }

    /**
     * Cria os contactos que se relacionam com o contacto da ficha. Esta relações podem ser profisionais ou interpessoais 
     * conforme definido pelo parametro $service.
     *
     * @param string  $id_contact - idenfificador único do contacto
     * @param string $dpto - só utilizado em "PROFI" é o departamento onde a pessoa trabalha
     * @param type $job - para "PROFI" é a profissão da pessoa. Para "RELAT" representa o realcionamento entes os contatos
     * @param type $ret_type - define onde será usado o contato. Se em modo de edição se na ficha de apresentação
     * @param string $service - indica o tipo de resposta esperada . "PROFI" para dados de ligação profissional, "RELAT" para relacionamentos interpessoais
     * 
     * @return NULL|string html em caso de sucesso
     */
    public function contact_for_module($id_contact, $dpto = NULL, $job = NULL, $ret_type = NULL, $service = "PROFI")
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #Procura os dados do contacto na base de dados
        try
        {
            $contact_data = $this->query_contact($id_contact);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage() . __LINE__);
        }

        try
        {
            $o_img = new GestImage();
            $img   = $o_img->send_images_json($contact_data[$this->contjs['admin']['private']['icon']], "src", FALSE, 0);
        }
        catch (Exception $exp)
        {
            $img = "imagens/sem_photo.png";
        }

        $mail   = NULL;
        $type   = NULL;
        $movel  = NULL;
        $movel2 = NULL;
        $movel3 = NULL;
        $face   = NULL;
        $google = NULL;
        $twit   = NULL;
        $linked = NULL;

        $name = (isset($contact_data["contactos.nome"])) ? $contact_data["contactos.nome"] : NULL;

        $surname = (isset($contact_data["contactos.apelido"])) ? $contact_data["contactos.apelido"] : NULL;

        $full_name = $name . " " . $surname;

        if (!empty($contact_data["contactos.id"]))
        {
            $id = $contact_data["contactos.id"];
        }
        else
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        if (!empty($contact_data["contactos.mail"]))
            $mail = $contact_data["contactos.mail"];

        if (!empty($contact_data[$this->typedb]))
            $type = $contact_data[$this->typedb];

        if (!empty($contact_data["contactos.telemovel"]))
            $movel = $contact_data["contactos.telemovel"] . " /";

        if (!empty($contact_data["contactos.telemovel2"]))
            $movel2 = $contact_data["contactos.telemovel2"] . " /";

        if (!empty($contact_data["contactos.telemovel3"]))
            $movel3 = $contact_data["contactos.telemovel3"];

        $fone = (!empty($contact_data["contactos.telefone"])) ? $contact_data["contactos.telefone"] : "";

        if (!empty($contact_data["contactos.facebook"]))
            $face = "<a href='" . $contact_data["contactos.facebook"] . "' target='_blank'>Facebook</a>";

        if (!empty($contact_data["contactos.linkedin"]))
            $linked = "<a href='" . $contact_data["contactos.linkedin"] . "' target='_blank'>Linkedin</a>";

        if (!empty($contact_data["contactos.google_plus"]))
            $google = "<a href='" . $contact_data["contactos.google_plus"] . "' target='_blank'>Google+</a>";

        if (!empty($contact_data["contactos.twitter"]))
            $twit = "<a href='" . $contact_data["contactos.twitter"] . "' target='_blank'>Twitter</a>";

        $cont = NULL;

        if ($ret_type == "SHEET")
        {
            $cont = '<div class="filehalfdiv"><div class="filedivimg" data-id="' . $id . '"><img src="' . $img . '" data-id="' . $id . '" id="mod:' . $id . '" class="modimg" data-action="contact"></div><div class="filedivtext"><p>' . $full_name . '</p><p>' . $dpto . '</p><p>' . $job . '</p><p>' . $mail . '</p><p>' . $movel . ' ' . $movel2 . ' ' . $movel3 . '</p><p>' . $fone . '</p></div><div class="filedivsocial">' . $face . ' ' . $linked . ' ' . $google . ' ' . $twit . '</div></div>';
        }
        else
        {

            if ($service === "RELAT")
            {
                $cont = '<div class="dvB terco"><img class="ig15A" data-action="delthis" src="imagens/minidel.png" draggable="false"><div class="wterco"><img src="' . $img . '" class="modimg"><p>' . $full_name . '</p><input type="hidden" name="mod_relation_id[' . $id . ']" value="' . $id . '"><label>Relacionamento</label><input name="relacoes_relacao[' . $id . ']" value="' . $job . '"></div></div>';
            }

            if ($service === "PROFI")
            {
                $cont = '<div class="dvB terco"><img class="ig15A" data-action="delthis" src="imagens/minidel.png" draggable="false"><div class="wterco"><img src="' . $img . '" class="modimg"><p>' . $full_name . '</p><input type="hidden" name="mod_' . $type . '_id[' . $id . ']" value="' . $id . '"><label>Departamento</label><input name="modules_department[' . $id . ']" value="' . $dpto . '"><label>Cargo</label><input name="modules_job[' . $id . ']" value="' . $job . '"></div></div>';
            }
        }
        return $cont;
    }

    /**
     * Cria uma lista de de grupos
     *
     * @uses Logex::$tcnx, Core::mess_alert();
     *
     * @return obejct json
     */
    public function goups()
    {
        if (!parent::check())
            return parent::mess_alert("CONT" . __LINE__);

        $group = NULL;
        $reln     = NULL;
        $rel      = NULL;

        try
        {
            $rows = parent::make_call("spContactsGroups");

            foreach ($rows as $rel)
            {
                if ($rel['grupo'])
                   $group[] = $rel['grupo'];
            }
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        if (is_array($group))
        {

            $reln['options'] = $group;
        }
        else
        {

            $reln['options'] ='';
        }

        $result = json_encode($reln);

        return $result;
    }

    /**
     * Estatisticas dos contatos
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     *
     * @return obejct json
     */
    public function stats()
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $chart = NULL;

        $charts       = array("Categorias", "Relacionamentos", "Paises", "Cidades", "Empresa areas de atividade");
        $count_charts = 0;

        $query = "SELECT categoria, count(categoria) as c_cat FROM contactos GROUP BY categoria ORDER BY c_cat DESC;";
        $query .= "SELECT grupo, count(grupo) as c_cat FROM contactos GROUP BY grupo ORDER BY c_cat DESC;";
        $query .= "SELECT pais, count(pais) as c_cat FROM contactos GROUP BY pais ORDER BY c_cat DESC;";
        $query .= "SELECT cidade, count(cidade) as c_cat FROM contactos GROUP BY cidade ORDER BY c_cat DESC;";
        $query .= "SELECT ramo_actividade, count(ramo_actividade) as c_cat FROM contactos WHERE categoria='empresa' GROUP BY ramo_actividade ORDER BY c_cat DESC";

        $idbcon = new mysqli(_LC, _US, _PS, _DB);

        if (!$idbcon)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $idbcon->set_charset("utf-8");

        if ($idbcon->multi_query($query))
        {
            do
            {
                $data   = NULL;
                $r_stat = $idbcon->store_result();

                if ($r_stat)
                {
                    while ($stats = $r_stat->fetch_array())
                    {
                        $data .= '"' . $stats[0] . '":"' . $stats[1] . '",';
                    }

                    $data = trim($data, ",");
                    $chart .= '"' . $charts[$count_charts] . '":{"type":"pie","data":{' . $data . '}},';

                    $count_charts++;

                    $r_stat->free();
                }
            }
            while ($idbcon->next_result());
        }
        else
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        $rtr_stats = '{' . trim($chart, ",") . '}';

        $idbcon->close();

        return $rtr_stats;
    }

    /**
     * Apaga um contacto da base de dados.
     *
     * @uses Logex::check(), Employees::delete_employee(), Core::mess_alert(), OperationsBar::delete_item()
     *
     * @param string $id_contact - id do contato
     *
     * @return string json
     *
     */
    public function delete_contact($id_contact)
    {

        if (!parent::check())
            return parent::mess_alert("CON" . __LINE__);

        if (!$id = parent::id($id_contact))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        //apaga o contacto
        $del_contact = new OperationsBar($this->contjs);
        $delet       = $del_contact->delete_item("spDeleteContact", $id);

        if (!$delet)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        return $delet;
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

            $op["state"]   = array("pais" => "distrito");
            $op["city"]    = array("distrito" => "cidade");
            $op["village"] = array("cidade" => "freguesia");

            return $this->optionsDis("contactos", $op);
        }
    }

    /**
     *
     * @return type
     */
    public function show_notes()
    {
        if (!parent::check())
            return NULL;

        if (!$id = $this->id($_POST['toke']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $notes = new GestNotes();

        try
        {
            return $notes->show_notes($this->contjs['notes'], $id);
        }
        catch (Exception $ex)
        {
            return parent::mess_alert($ex->getMessage());
        }
    }

    /**
     * Procura um contato na base de dados
     *
     * @uses Core::id()
     * @uses Core::make_call()
     *
     * @param string $id_contact - id do contato
     *
     * @throws Exception
     *
     * @return array - resultado da pesquisa na base de dados
     *
     */
    protected function query_contact($id_contact)
    {

        if (!$id = parent::id($id_contact))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        try
        {
            $rows = parent::make_call("spContactData", array($id));

            if (!isset($rows[0]))
                throw new Exception($this->exp_prefix . __LINE__, 1);

            return $rows[0];
        }
        catch (Exception $exp)
        {
            throw new Exception($this->exp_prefix . __LINE__, 1);
        }
    }

    /**
     *
     * @param type $id_contact
     * @param type $mode
     * @return type
     * @throws Exception
     */
    protected function query_full_contact($id_contact, $mode = NULL)
    {
        if (!$id = parent::id($id_contact))
            throw new Exception($this->exp_prefix . __LINE__, 1);
        try
        {
            $contact_data = $this->query_contact($id_contact);

            $contact_data["modules.empresa"] = $this->edit_modules($id_contact, $mode);
            $contact_data["relations.relat"] = $this->edit_relations($id_contact, $mode);

            return $contact_data;
        }
        catch (Exception $exp)
        {
            throw new Exception($exp->getMessage(), 1);
        }
    }

    /**
     * Cria a array de configuração para criar uma ficha de operações em conformidade com o tipo de contato
     *
     * @param string $type_contact - tipo do contato (pessoa ou empresa)
     *
     * @return array - com tipo de contacto e a array de configuração
     *
     */
    protected function configure_operations_sheet($type_contact)
    {

        $type = (strtolower($type_contact) === "empresa") ? "empresa" : "pessoa";

        if ($type === "pessoa")
        {
            unset($this->contjs['content']['identi_company']);
            unset($this->contjs['content']['data_company']);
            unset($this->contjs['content']['company_prof']);
        }
        else
        {
            $this->contjs['content']['identi'] = $this->contjs['content']['identi_company'];
            $this->contjs['content']['data']   = $this->contjs['content']['data_company'];
            $this->contjs['content']['prof']   = $this->contjs['content']['company_prof'];

            unset($this->contjs['content']['identi_company']);
            unset($this->contjs['content']['data_company']);
            unset($this->contjs['content']['company_prof']);
        }

        $data['type']   = $type;
        $data['config'] = $this->contjs;

        return $data;
    }

    /**
     * retorna os dados de um contacto para inserir na ficha
     *
     * @param array $contact_data - resultado da pesquisa na base de dados
     * @param object $sheet - objeto ItemSheet
     *
     * @throws Exception
     *
     * @return array configuração do contato conforme seja da tipo pessoa ou empresa
     *
     */
    protected function prep_file(&$contact_data, $sheet)
    {
        #VERIFICA A CATEGORIA
        $category = FALSE;

        if (isset($contact_data[$this->typedb]))
            $category = strtolower($contact_data[$this->typedb]);

        if (!$category)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        //chave do campo de envio da newsletter
        $send_news_db = $this->contjs['admin']['public']['send_news']['db'];

        //chave do campo de email
        $mail_db = $this->contjs['content']['contacts']['mail']['db'];

        //verifica se está hablitado para receber newsletter
        $contact_data[$send_news_db] = ($contact_data[$send_news_db]) ? "Sim" : "Não";

        //verifica se tem email definido
        if (!$contact_data[$mail_db])
            $contact_data[$send_news_db] = "Não tem endereço de email.";

        if ($category === "pessoa")
            return $this->make_file_person($contact_data, $sheet);

        if ($category == "empresa")
            return $this->make_file_company($contact_data, $sheet);

        throw new Exception($this->exp_prefix . __LINE__, 1);
    }

    /**
     * Cria o conteúdo da ficha de apresentação de uma pessoa
     *
     * @uses ItemSheet::make_sheet_content()
     * @uses Contacts::query_contact()
     *
     * @param array $db_result - resultado da pesquisa na base de dados
     * @param ItemSheet $ISheet - instancia de ItemSheet
     *
     * @return string - estrutura html com o conteúdo da ficha
     *
     */
    protected function make_file_person(array $db_result, ItemSheet $ISheet)
    {
        $pers = NULL;

        $personal = &$this->contjs['content']['identi'];

        $name    = $personal['title']['db'];
        $surname = $personal['surname']['db'];

        //manipulação do nome do contato para o nome completo aperecer em apenas um campo largo
        $db_result[$name]          = $db_result[$name] . " " . $db_result[$surname];
        $personal['title']['type'] = "L_INPUT";
        unset($personal['surname']);

        $person['personal'] = $this->contjs['content']['identi'];
        $conts['contacts']  = $this->contjs['content']['contacts'];
        $local['local']     = $this->contjs['content']['local'];
        $data['data']       = $this->contjs['content']['data'];
        $web['web']         = $this->contjs['content']['web'];
        $prof['prof']       = $this->contjs['content']['prof'];
        $rel['relat']       = $this->contjs['content']['relat'];

        $pers = $ISheet->make_sheet_content($person, $db_result) . $ISheet->make_sheet_content($conts, $db_result) . $ISheet->make_sheet_content($local, $db_result) . $ISheet->make_sheet_content($data, $db_result) . $ISheet->make_sheet_content($web, $db_result) . $ISheet->make_sheet_content($prof, $db_result) . $ISheet->make_sheet_content($rel, $db_result);

        return $pers;
    }

    /**
     * Cria o conteúdo da ficha de apresentação de uma empresa
     *
     * @uses ItemSheet::make_sheet_content();
     *
     * @param array $db_result - resultado da pesquisa na base de dados
     * @param ItemSheet $ISheet - instancia de ItemSheet
     *
     * @return string - estrutura html com o conteúdo da ficha
     *
     */
    protected function make_file_company(array $db_result, ItemSheet $ISheet)
    {
        $person['personal']   = $this->contjs['content']['identi_company'];
        $conts['contacts']    = $this->contjs['content']['contacts'];
        $local['local']       = $this->contjs['content']['local'];
        $data['data']         = $this->contjs['content']['data_company'];
        $web['web']           = $this->contjs['content']['web'];
        $prof['company_prof'] = $this->contjs['content']['company_prof'];
        $rel['relat']         = $this->contjs['content']['relat'];

        $company = $ISheet->make_sheet_content($person, $db_result) . $ISheet->make_sheet_content($conts, $db_result) . $ISheet->make_sheet_content($local, $db_result) . $ISheet->make_sheet_content($data, $db_result) . $ISheet->make_sheet_content($web, $db_result) . $ISheet->make_sheet_content($prof, $db_result) . $ISheet->make_sheet_content($rel, $db_result);

        return $company;
    }

    /**
     * extrai os dados da página do facebook do contato se for informada a url da página
     *
     * @param array $post_fields - campos do formulário
     * @param numeric $id - id do item que está a ser actualizado
     *
     */
    private function social_data(array $post_fields, $id = null)
    {

        $id_facebook = NULL;
        $face_value  = NULL;
        $face_url    = FALSE;

        foreach ($post_fields as $value)
        {

            if (!$value)
                continue;

            if (!is_string($value))
                continue;

            if (!parent::validate_url($value))
                continue;

            if (!$network = parse_url($value, PHP_URL_HOST))
                continue;

            if ($network == "www.facebook.com" || $network == "facebook.com")
            {
                $face_url = $value;
            }
        }

        //TODO decidir de gravo o id do facebook na base de dados ou não.
        /* if (!$face_url)
          $_POST['private_facebook_id'] = "";

          if ($id) {
          $rslt = Logex::$tcnx -> query("SELECT facebook_id FROM " . $this -> table . " WHERE " . $this -> idb . "=$id AND facebook='$face_url' AND facebook_id <>'' ");

          if ($rslt)
          $bd_id_facebook = $rslt -> fetch_array();

          if ($bd_id_facebook[0])
          $id_facebook = $bd_id_facebook[0];
          } */

        if (!$id_facebook)
        {
            try
            {

                $social      = new GestSocial();
                $id_facebook = $social->find_facebook_id($face_url);

                $_POST['private_facebook_id'] = $id_facebook;

                $fd = $social->facebook_image($id_facebook);

                $ob = NULL;

                if (!empty($fd['image']))
                    $ob .= '"foto":"' . $fd['image'] . '",';
                if (!empty($fd['sexo']))
                    $ob .= '"sexo":"' . $fd['sexo'] . '",';
                if (!empty($fd['nome']))
                    $ob .= '"nome":"' . $fd['nome'] . '",';
                if (!empty($fd['apelido']))
                    $ob .= '"apelido":"' . $fd['apelido'] . '",';
                if (!empty($fd['pais']))
                    $ob .= '"pais":"' . $fd['pais'] . '",';
                if (!empty($fd['cidade']))
                    $ob .= '"cidade":"' . $fd['cidade'] . '",';
                if (!empty($fd['rua']))
                    $ob .= '"rua":"' . $fd['rua'] . '",';
                if (!empty($fd['cp']))
                    $ob .= '"codigo postal":"' . $fd['cp'] . '",';
                if (!empty($fd['nasc']))
                    $ob .= '"data de nascimento":"' . $fd['nasc'] . '",';
                if (!empty($fd['site']))
                    $ob .= '"website":"' . $fd['site'] . '",';
                if (!empty($fd['fone']))
                    $ob .= '"telefone":"' . $fd['fone'] . '",';

                return '{"redes":{"facebook":{' . trim($ob, ",") . '}}}';
            }
            catch (Exception $exp)
            {
                $err = $exp->getMessage();
                return FALSE;
            }
        }
    }

    /**
     *
     */
    private function return_facebook_data($id_facebook)
    {
        try
        {
            $social = new GestSocial();
            $fd     = $social->facebook_image($id_facebook);

            $ob = NULL;

            if ($fd['image'])
                $ob .= '"foto":"' . $fd['image'] . '",';
            if ($fd['sexo'])
                $ob .= '"sexo":"' . $fd['sexo'] . '",';
            if ($fd['nome'])
                $ob .= '"nome":"' . $fd['nome'] . '",';
            if ($fd['apelido'])
                $ob .= '"apelido":"' . $fd['apelido'] . '",';
            if ($fd['pais'])
                $ob .= '"pais":"' . $fd['pais'] . '",';
            if ($fd['cidade'])
                $ob .= '"cidade":"' . $fd['cidade'] . '",';
            if ($fd['rua'])
                $ob .= '"rua":"' . $fd['rua'] . '",';
            if ($fd['cp'])
                $ob .= '"codigo postal":"' . $fd['cp'] . '",';
            if ($fd['nasc'])
                $ob .= '"data de nascimento":"' . $fd['nasc'] . '",';
            if ($fd['site'])
                $ob .= '"website":"' . $fd['site'] . '",';
            if ($fd['fone'])
                $ob .= '"telefone":"' . $fd['fone'] . '",';

            return '{"redes":{"facebook":{' . trim($ob, ",") . '}}}';
        }
        catch (Exception $exp)
        {
            throw new Exception($exp->getMessage(), 1);
        }
    }

    /**
     * grava os dados retirados do facebook na base de dados
     */
    private function facebook_data()
    {

        if (!empty($_POST['facebook_foto']))
        {
            unset($_POST['foto_contato']);
            $_POST['foto_contato'][] = $_POST['facebook_foto'];
        }

        if (!empty($_POST['facebook_sexo']))
            $_POST['personal_sex'] = $_POST['facebook_foto'];

        if (!empty($_POST['facebook_nome']))
            $_POST['identi_company_title'] = $_POST['identi_title']         = $_POST['facebook_nome'];

        if (!empty($_POST['facebook_apelido']))
            $_POST['identi_surname'] = $_POST['facebook_apelido'];

        if (!empty($_POST['facebook_pais']))
            $_POST['local_country'] = $_POST['facebook_pais'];

        if (!empty($_POST['facebook_cidade']))
            $_POST['local_city'] = $_POST['facebook_cidade'];

        if (!empty($_POST['facebook_rua']))
            $_POST['local_address'] = $_POST['facebook_rua'];

        if (!empty($_POST['facebook_codigo_postal']))
            $_POST['local_postal'] = $_POST['facebook_codigo_postal'];

        if (!empty($_POST['facebook_data_de_nascimento']))
            $_POST['personal_birthday'] = $_POST['company_birthday']  = $_POST['facebook_data_de_nascimento'];

        if (!empty($_POST['facebook_website']))
            $_POST['web_webpage']     = $_POST['company_webpage'] = $_POST['facebook_website'];

        if (!empty($_POST['facebook_telefone']))
            $_POST['contacts_phone'] = $_POST['company_phone']  = $_POST['facebook_telefone'];

        /* if (empty($_POST['personal_profile']))
          $_POST['personal_profile'] = $fd['dados'];

          if (empty($_POST['company_profile']))
          $_POST['company_profile'] = $fd['dados']; */
    }

    /**
     *
     * @param type $toke
     * @param type $type - pode ter o valor "SHEET" ou diferente de "SHEET"
     * @return type
     * @throws Exception
     */
    private function edit_relations($toke, $type = NULL)
    {
        $modules = NULL;

        if (!$id = $this->id($toke))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        try
        {

            $item_content = parent::make_call("spContactRelations", array($id));
        }
        catch (Exception $exp)
        {
            throw new Exception($this->exp_prefix . __LINE__, 1);
        }

        if (!is_array($item_content))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        foreach ($item_content as $value)
        {
            if (!$value)
                continue;

            $mid = ($value["relacoes.cont1"] == $id) ? $value["relacoes.cont2"] : $value["relacoes.cont1"];

            if ($type == "SHEET")
            {
                $modules .= $this->contact_for_module($mid, NULL, $value["relacoes.relacao"], "SHEET", "RELAT");
            }
            else
            {
                $modules .= $this->contact_for_module($mid, NULL, $value["relacoes.relacao"], NULL, "RELAT");
            }
        }

        return $modules;
    }

    /**
     *
     * @param type $toke
     * @param type $type - pode ter o valor "SHEET" ou diferente de "SHEET"
     * @return type
     * @throws Exception
     */
    private function edit_modules($toke, $type = NULL)
    {
        $modules = NULL;

        if (!$id = $this->id($toke))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        try
        {

            $item_content = parent::make_call("spEmpresaPessoa", array($id));
        }
        catch (Exception $exp)
        {
            throw new Exception($this->exp_prefix . __LINE__, 1);
        }

        if (!is_array($item_content))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        foreach ($item_content as $value)
        {
            if (!$value)
                continue;

            $mid = ($value["modules.empresa"] != $id) ? $value["modules.empresa"] : $value["modules.pessoa"];

            if ($type == "SHEET")
            {
                $modules .= $this->contact_for_module($mid, $value["modules.departamento"], $value["modules.cargo"], "SHEET");
            }
            else
            {
                $modules .= $this->contact_for_module($mid, $value["modules.departamento"], $value["modules.cargo"]);
            }
        }

        return $modules;
    }

    /**
     *
     */
    private function query_modules()
    {

        if (!isset($_POST))
            return FALSE;

        $query = NULL;

        foreach ($_POST as $key => $value)
        {
            $match = NULL;

            if (preg_match("/mod_(empresa|pessoa)_id/", $key, $match))
            {
                foreach ($value as $vvalue)
                {
                    if ($match[1] == "pessoa")
                        $query .= "(" . $_POST['mod_pessoa_id'][$vvalue] . ",@id,'" . $_POST['modules_department'][$vvalue] . "','" . $_POST['modules_job'][$vvalue] . "'),";

                    if ($match[1] == "empresa")
                        $query .="(@id,'" . $_POST['mod_empresa_id'][$vvalue] . "','" . $_POST['modules_department'][$vvalue] . "','" . $_POST['modules_job'][$vvalue] . "'),";
                }
            }
        }

        $r_query = ($query) ? trim($query, ",") : NULL;

        return $r_query;
    }

    /**
     *
     */
    private function query_relation()
    {

        if (!isset($_POST))
            return FALSE;

        $query = NULL;

        foreach ($_POST as $key => $value)
        {
            if (preg_match("/mod_relation_id/", $key, $match))
            {
                foreach ($value as $vvalue)
                {
                    $query .= "(" . $_POST['mod_relation_id'][$vvalue] . ",@id,'" . $_POST['relacoes_relacao'][$vvalue] . "'),";
                }
            }
        }

        $r_query = ($query) ? trim($query, ",") : NULL;

        return $r_query;
    }

}

/**
 * Esta classe faz a gestão dos colaboradores.
 * 
 * Stored procedures necessárias:
 * - spColbData
 * - spColbFolders
 * - spChangeContactsFolders - comum com a classe Contacts
 * - spInsertColb
 * - spUpdateColb
 * - spDeleteColb
 * 
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version V1.00.030615
 * @since 03/06/2015
 * @license Todos os direitos reservados
 */
class Employees extends Contacts
{

    private $emp_config;
    private $table;
    public $cont_config;

    #coluna chave primaria na base de dados
    private $dbtoke;
    private $exp_prefix = "EMP";

    public function __construct()
    {

        parent::__construct();

        $this->emp_config = $this->json_file("JEMPLOYEES");

        $this->table = $this->emp_config['table'];

        $this->dbtoke = $this->emp_config['admin']['private']['toke'];

        $this->cont_config = $this->get_contact_config();
    }

    /**
     *
     */
    public function get_emp_config()
    {
        return $this->emp_config;
    }

    /**
     * Procura um colaborador na base de dados
     *
     * @uses Logex::$tcnx
     *
     * @param $idx - id do colaborador a procuras
     *
     * @return array - resultado da pesquisa na base de dados
     *
     */
    private function query_employee($idx, $mode = NULL)
    {
        if (!$id = (parent::id($idx)))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        try
        {
            $contact = $this->query_full_contact($id, $mode);
        }
        catch (Excecption $exp)
        {
            throw new Exception($exp->getMessage(), 1);
        }

        try
        {

            $employee = parent::make_call("spColbData", array($id));
        }
        catch (Exception $exp)
        {

            throw new Exception($this->exp_prefix . __LINE__, 1);
        }

        if (!is_array($employee) || !count($employee))
        {
            $employee = array();
        }
        else
        {
            $employee = $employee[0];
        }

        $final = array_merge($contact, $employee);

        if (!$final)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        return $final;
    }

    /**
     *
     * @return type
     */
    public function make_employees_folders()
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $folder = new GestFolders();
        return $folder->make_folders("spColbFolders");
    }

    /**
     *
     * @param type $toke
     * @param type $new_folder
     * @return type
     */
    public function change_employees_folders($toke, $new_folder)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = $this->id($toke))
            parent::mess_alert($this->exp_prefix . __LINE__);

        $n_folder = parent::validate_name($new_folder);

        $folder = new GestFolders();
        return $folder->change_folder("spChangeContactsFolders", array($id, $n_folder, "Colaborador", ''));
    }

    /**
     * Cria a ficha de apresentação de um colaborador
     *
     * @uses Logex::check()
     * @uses Employees::query_employee()
     * @uses Core::mess_alert()
     * @uses Contacts::prep_file()
     * @uses ItemSheet::make_sheet()
     * @uses ItemSheet::make_sheet_content()
     *
     * @param string $id_emp - id do colaborador. Valor da chave primária
     *
     * @return string - estrutura html com a ficha de apresentação
     *
     */
    public function make_emp_sheet($id_emp)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $employee_data = $this->query_employee($id_emp, "SHEET");

            #Define o estado do contato
            $this->cont_config['admin']['public']['status']['db'] = $this->emp_config['admin']['public']['status']['db'];

            $ISheet = new ItemSheet();

            if (isset($this->emp_config['content']['pt']))
                unset($this->emp_config['content']['access']);

            unset($this->emp_config['content']['access']['pass']);
            $this->emp_config['content']['access']['level']['type'] = "S_INPUT";

            $emp_content = $ISheet->make_sheet_content($this->emp_config['content'], $employee_data);

            $contact_content = parent::prep_file($employee_data, $ISheet);

            if ($contact_content)
                return $ISheet->make_sheet($this->cont_config, $employee_data, ($emp_content . $contact_content));

            return parent::mess_alert("EMP" . __LINE__);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }
    }

    /**
     * Cria ficha para adição de um novo contato
     *
     * @uses Logex::check(), Core::mess_alert(), Contacts::configure_operations_sheet(), ElementDatalist::make_datalist_options(), OperationsBar::add_item_sheet()
     *
     * @return string - estrutura html com a ficha
     *
     */
    public function add_employee()
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $contact_type = (strtolower($_POST['type']) === "empresa") ? "empresa" : "pessoa";

        $cont_config = parent::configure_operations_sheet($contact_type);

        $predef[$cont_config['config']['admin']['public']['category']['db']] = $contact_type;
        $predef[$cont_config['config']['admin']['public']['group']['db']]    = "Colaborador";

        $cont_config['config']['admin']['public']['status'] = $this->emp_config['admin']['public']['status'];
        $cont_config['config']['content']['access']         = $this->emp_config['content']['access'];

        $datalist = New ElementDatalist();

        foreach ($GLOBALS['LANGPAGES'] as $lang)
        {
            if (isset($this->emp_config['content'][$lang]))
            {
                $datalist->make_datalist_options($this->emp_config['content'][$lang], $this->emp_config['table'], $employee_data);

                $cont_config['config']['content'][$lang] = $this->emp_config['content'][$lang];
            }
        }

        unset($datalist);

        $add_sheet = new OperationsBar($cont_config['config']);

        return $add_sheet->add_item_sheet($predef);
    }

    /**
     * Cria ficha para a edição dos dodos de um colaborador
     *
     * @uses Logex::check(), Core::mess_alert(), Contacts::configure_operations_sheet(), ElementDatalist::make_datalist_options(), OperationsBar::add_item_sheet(), Employee::query_emplyoee()
     *
     * @param string $id_emp - id do colaborador
     *
     * @return string - estrutura html com a ficha de edição
     *
     */
    public function edit_employee($id_emp)
    {
        if (!parent::check())
            return parent::mess_alert("EMP" . __LINE__);
        try
        {

            $employee_data = $this->query_employee($id_emp);

            if (empty($employee_data['contactos.categoria']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $cont_config = parent::configure_operations_sheet($employee_data['contactos.categoria']);

            $cont_config['config']['admin']['public']['status'] = $this->emp_config['admin']['public']['status'];

            $cont_config['config']['content']['access'] = $this->emp_config['content']['access'];

            $datalist = New ElementDatalist();

            foreach ($GLOBALS['LANGPAGES'] as $lang)
            {
                if (isset($this->emp_config['content'][$lang]))
                    $datalist->make_datalist_options($this->emp_config['content'][$lang], $this->emp_config['table'], $employee_data);

                @$cont_config['config']['content'][$lang] = $this->emp_config['content'][$lang];
            }

            unset($datalist);

            $add_sheet = new OperationsBar($cont_config['config']);
            $add_sheet->set_mode("UPDATE");
            return $add_sheet->edit_item_sheet($employee_data);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }
    }

    /**
     * Inicia a gravação de um contato na base de dados
     *
     * @return string - em caso de sucesso json com os dados, em caso de falha json com mensagem de erro
     *
     */
    public function insert_employee()
    {
        #verifica se tem sessão válida
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #verifica se existe dados
        if (!isset($_POST))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #verifica tipo de operação
        $save_mode = ($_POST['filemode'] == "UPDATE" || $_POST['filemode'] == "ADD") ? $_POST['filemode'] : FALSE;

        if (!$save_mode)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if ($_POST["public_group"] != "Colaborador" && $save_mode == "ADD")
            return '{"errormess":"Está a tentar salvar um contato que tem um GRUPO diferente de COLABORADOR. \\n\\n Por favor, utilize o modulo CONTACTOS para realizar esta operação."}';

        #verifica se os dados de acesso são válidos
        if ($clean_input = $this->validate_access())
            return $clean_input;

        #verifica se tem id definido
        $identy = parent::id($_POST['toke']);

        #realiza a operação nas tabelas contactos
        $contact = parent::insert_contact($save_mode, "Colaborador");

        #testa o resultado da operação nas tabelas dos contactos
        if (!$json = json_decode($contact, TRUE))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #confirmar dados das redes sociais
        if (isset($json['redes']))
            return $contact;

        #confirmar se existe algum erro
        if (isset($json['alert']))
            return $contact;

        #verifica se o id é valido
        if (!$id = parent::id($json['result'][1]))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $query = NULL;

        $emp_saver = new OperationsBar($this->emp_config);

        $query = $emp_saver->make_query();

        #confirma se o resultado é o esperado
        if (!isset($query['colaboradores']))
            return '{"alert":"Foi impossivel gravar os dados de acesso do COLABORADOR. \\n\\n No entando alguns dados foram gravados. \\n\\n Por favor, recarregue o modulo, abra a ficha do Colaborador, atualize os dados e tente grava de novo. \\n \\n Se o erro continuar, entre em contato com a assistência técnica.' . $this->exp_prefix . __LINE__ . '"}';

        if ($save_mode == "ADD" && !$identy)
        {

            try
            {
                $item_content = parent::make_call("spInsertColb", array($query['colaboradores'], $_POST['access_pass'], $_POST['access_nick'], $id));
            }
            catch (Exception $exp)
            {
                throw new Exception($this->exp_prefix . __LINE__, 1);
            }
        }

        if ($save_mode == "UPDATE" && $identy)
        {
            if ($json['result'][2] != "Colaborador")
                return '{"result":["",""]}';

            try
            {
                $item_content = parent::make_call("spUpdateColb", array($query['colaboradores'], $_POST['access_pass'], $_POST['access_nick'], $id));
            }
            catch (Exception $exp)
            {
                throw new Exception($this->exp_prefix . __LINE__, 1);
            }
        }

        if (!$ret = json_decode($item_content[0]['ret'], TRUE))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (isset($ret['mgp_error']))
        {
            if ($mess = $emp_saver->db_error_message($ret['mgp_error']))
                return $mess;

            return '{"alert":"Foi impossivel gravar os dados de acesso do COLABORADOR mas alguns dados foram gravados. \\n\\n Por favor, recarregue o modulo, abra a ficha do Colaborador, atualize os dados e tente grava de novo. \\n \\n Se o erro continuar, entre em contato com a assistência técnica. ' . $this->exp_prefix . __LINE__ . '"}';
        }

        if (isset($ret['opsat']) && $ret['opsat'] == "ok")
            return $contact;

        return '{"alert":"Foi impossivel gravar os dados de acesso do COLABORADOR  mas alguns dados foram gravados. \\n\\n Por favor, recarregue o modulo, abra a ficha do Colaborador, atualize os dados e tente grava de novo. \\n \\n Se o erro continuar, entre em contato com a assistência técnica. ' . $this->exp_prefix . __LINE__ . '"}';
    }

    /**
     * Apaga um colaborador na tabela na base de dados e passa a categoria desse contato na tabela contatos para "EX-COLABORADOR".
     *
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core:id()
     * @uses Core::make_call()
     *
     * @param string $id_employee - valor da chave primária da tabela colaboradores.
     *
     * @return json - com mensagem de erro em caso de falha ou objeto "result" em caso de sucesso
     *
     */
    public function delete_employee($id_employee)
    {
        if (!parent::check())
            return parent::mess_alert("EMP" . __LINE__);

        if (!$emp_id = parent::id($id_employee))
            return parent::mess_alert("EMP" . __LINE__);

        try
        {
            $result = parent::make_call("spDeleteColb", array($emp_id));
        }
        catch (Exception $ex)
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }

        if (isset($result[0]))
        {
            if (!$resp = json_decode($result[0]['ret'], TRUE))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            if (isset($resp['mgp_error']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            return '{"result":["' . $resp['folder'] . '","' . $resp['id'] . '"]}';
        }
        else
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }
    }

    /**
     * Sanatiza os inputs da tabela de colaboradores e valida a senha e o nick
     *
     * @uses Logex::$tcnx, Anti::validate_name
     *
     * @return boolean|string - False se não encotra erros ou mensagem de erros
     */
    private function validate_access()
    {

        $vlevel                 = (isset($_POST['access_level'])) ? filter_var($_POST['access_level'], FILTER_VALIDATE_INT) : "''";
        $_POST['public_status'] = (isset($_POST['public_status']) && $_POST['public_status'] === "online") ? "online" : "offline";

        if ($vlevel < 0 || $vlevel > 4)
            return '{"alert":"Não foi possivel guardar os dados de acesso. EMP' . __LINE__ . '"}';

        if (!empty($_POST['access_nick']) && !empty($_POST['access_pass']))
        {

            $_POST['access_nick'] = ($this->validate_name($_POST['access_nick'])) ? $this->validate_name($_POST['access_nick']) : NULL;
            $_POST['access_pass'] = ($this->validate_name($_POST['access_pass'])) ? $this->validate_name($_POST['access_pass']) : NULL;

            if (empty($_POST['access_pass']))
                return '{"errormess":"Senha inválida."}';

            if (empty($_POST['access_nick']))
                return '{"errormess":"Nick inválido."}';
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
        $emp_table     = $this->table;

        $query = "
                SELECT id,imagem, nome, apelido,categoria, order_index FROM $contact_table
                INNER JOIN $emp_table
                ON $contact_table.id = $emp_table.id_contacto
                ORDER BY order_index DESC
            ";

        if (!$q = Logex::$tcnx->query($query))
            return parent::mess_alert("EMP" . __LINE__);

        $or = NULL;
        while ($od = $q->fetch_array())
        {

            if (!$od[1])
                $ig = ($od[4] === "Empresa") ? "../imagens/ftEmp.png" : "../imagens/ftCont.png";

            $images = new GestImage();

            $img = ($od[1]) ? '"' . $images->send_images_json($od[1], "src", NULL, 0, NULL) . '"' : '"' . $ig . '"';
            $or .= ",[\"$od[0]\",$img,\"$od[2]\"]";
        }

        return '{"result":[' . ltrim($or, ",") . ']}';
    }

}

/**
 * Esta classe faz a gestão dos clientes.
 * 
 * Stored procedures necessárias:
 * - spClientData
 * - spClientFolders
 * - spChangeContactsFolders - comum com a classe Contacts
 * - spInsertClient
 * - spUpdateClient
 * - spDeleteClient
 * 
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author Mgpdinamic.com
 *
 * @version V1.10.100615
 * @since 10/06/2015
 * @license Todos os direitos reservados
 */
class Clients extends Contacts
{

    
    /**
     *  Array de configuração do cliente
     * 
     * @var array 
     */
    private $cli_config;

    /**
     * Tabela do modulo
     * 
     * @var string
     */
    private $cli_table;
    
    /**
     * Coluna chave primaria na base de dados
     * 
     * @var string
     */
    private $dbtoke;

    /**
     * Código de erro do modulo
     * 
     * @var type 
     */
    private $exp_prefix = "CLIE";
    
    /*
     * Nome para para seleção dos elementos do grupo. É o nome que aparece no botão do separador Empresa.
     * Com esta variável é possivel dar outro nome ao grupo especial "Clientes".
     * ! Atenção prcisa também alterar este nome nas stored procedures spInsertContact e spUpdateContact 
     * ! e fazer com que este nome seja um opção definida no campo grupos da ficha de clientes
     *
     */
    static private $group_name = "Cliente";

    public function __construct()
    {

        parent::__construct();

        $this->cli_config = $this->json_file("JCLIENTS");

        $this->cli_table = $this->cli_config['table'];

        $this->dbtoke = $this->cli_config['admin']['private']['toke'];
    }

    /**
     * retira os dados de um cliente na base de dados
     *
     * @uses Logex::$tcnx()
     *
     * @param $idx - id do colaborador a procuras
     * @param $mode - define se os dados irão ser usados em edição ou na ficha ($mode="SHEET");
     *
     * @thorw Exception
     *
     * @return array - resultado da pesquisa na base de dados
     *
     */
    private function query_client($idx, $mode = NULL)
    {
        if (!$id = (parent::id($idx)))
            throw new Exception($this->exp_prefix . __LINE__, 1);

        try
        {
            $contact = $this->query_full_contact($id, $mode);
        }
        catch (Excecption $exp)
        {
            throw new Exception($exp->getMessage(), 1);
        }

        try
        {
            $result = parent::make_call("spClientData", array($id));
        }
        catch (Exception $exp)
        {
            throw new Exception($this->exp_prefix . __LINE__, 1);
        }

        if (!is_array($result) || !count($result))
        {
            $client = array();
        }
        else
        {
            $client = $result[0];
        }

        $final = array_merge($contact, $client);

        if (!$final)
            throw new Exception($this->exp_prefix . __LINE__, 1);

        return $final;
    }

    /**
     * Cria as pastas dos clientes
     *
     * @uses GestFolder::set_folder_call(), GestFolder::make_folders()
     *
     * @return string - objeto json com uma array de objetos {"id":"","status":"","name":"","image":""}
     *
     */
    public function client_folders()
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $folder = new GestFolders();
        $f      = $folder->make_folders("spClientFolders");
        return $f;
    }

    /**
     *
     * @param type $toke
     * @param type $new_folder
     * @return type
     */
    public function change_client_folders($toke, $new_folder)
    {

        if (!parent::check())
            return parent::mess_alert("EMP" . __LINE__);

        if (!$id = $this->id($toke))
            parent::mess_alert($this->exp_prefix . __LINE__);

        $n_folder = parent::validate_name($new_folder);

        $folder = new GestFolders();
        return $folder->change_folder("spChangeContactsFolders", array($id, $n_folder, Clients::$group_name, ''));
    }

    /**
     * Cria a ficha de apresentação de um cliente
     *
     * @uses Logex::check(), Clients::query_client(), Core::mess_alert(), Core::for_module(), Contacts::prep_file(),ItemSheet::make_sheet()
     *
     * @param string $cli_id - id do coliente. Valor da chave primária
     *
     * @return string - estrutura html com a ficha de apresentação
     *
     */
    public function client_sheet($cli_id)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);
        try
        {
            $client_data = $this->query_client($cli_id, "SHEET");

            $ISheet = new ItemSheet();

            if (isset($this->cli_config['content']['pt']))
                unset($this->cli_config['content']['access']);

            unset($this->cli_config['content']['access']['pass']);
            $this->cli_config['content']['access']['level']['type'] = "S_INPUT";

            #cria o conteudo especifico do cliente
            $module_data = $ISheet->make_sheet_content($this->cli_config['content'], $client_data);

            $company_content = parent::prep_file($client_data, $ISheet);

            if ($company_content)
                return $ISheet->make_sheet(parent::get_contact_config(), $client_data, ($module_data . $company_content));

            return parent::mess_alert($this->exp_prefix . __LINE__);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }
    }

    /**
     * Cria ficha para adição de um novo cliente
     *
     * @uses Logex::check(), Core::mess_alert(), Contacts::configure_operations_sheet(), OperationsBar::add_item_sheet()
     *
     * @return string - estrutura html com a ficha
     *
     */
    public function add_client()
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!isset($_POST['type']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $type = ($_POST['type'] == "Empresa") ? "empresa" : "pessoa";

        $contact = parent::configure_operations_sheet($type);

        $predef['contactos.categoria'] = $type;
        $predef['contactos.grupo']     = Clients::$group_name;

        $contact['config']['content'] = array_merge($contact['config']['content'], $this->cli_config['content']);

        $add_sheet = new OperationsBar($contact['config']);

        return $add_sheet->add_item_sheet($predef);
    }

    /**
     * Cria ficha para a edição dos dados de um cliente
     *
     * @uses Logex::check(), Core::mess_alert(), Contacts::configure_operations_sheet(), Contacts::get_contact_config(), Core::for_module(), OperationsBar::add_item_sheet(), Clients::query_client()
     *
     * @param string $id_emp - id do cliente. Valor da chave primária
     *
     * @return string - estrutura html com a ficha de edição
     *
     */
    public function edit_client($id_emp)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);
        try
        {
            $client_data = $this->query_client($id_emp);

            if (empty($client_data['contactos.categoria']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            $contact = parent::configure_operations_sheet(strtolower($client_data['contactos.categoria']));

            $contact['config']['content'] = array_merge($contact['config']['content'], $this->cli_config['content']);

            $add_sheet = new OperationsBar($contact['config']);
            $add_sheet->set_mode("UPDATE");

            return $add_sheet->edit_item_sheet($client_data);
        }
        catch (Exception $exp)
        {
            return parent::mess_alert($exp->getMessage());
        }
    }

    /**
     * Inicia a gravação de um contato na base de dados
     *
     * @return string - em caso de sucesso json com os dados, em caso de falha json com mensagem de erro
     *
     */
    public function insert_client()
    {
        #verifica se tem sessão válida
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #verifica se existem dados
        if (!isset($_POST))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #verifica tipo de operação
        $save_mode = ($_POST['filemode'] == "UPDATE" || $_POST['filemode'] == "ADD") ? $_POST['filemode'] : FALSE;

        if (!$save_mode)
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if ($_POST["public_group"] != Clients::$group_name && $save_mode == "ADD")
            return '{"errormess":"Está a tentar salvar um contato que tem um GRUPO diferente de '. strtoupper (Clients::$group_name) .'. \\n\\n Por favor, utilize o modulo CONTACTOS para realizar esta operação."}';

        #verifica se os dados de acesso são válidos
        $clean_input = $this->validate_access();
        if ($clean_input)
            return $clean_input;

        #verifica se tem id definido
        $identy = parent::id($_POST['toke']);

        #realiza a operação nas tabelas contactos
        $contact = parent::insert_contact($save_mode);

        #testa o resultado da operação nas tabelas dos contactos
        if (!$json = json_decode($contact, TRUE))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        #confirmar dados das redes sociais
        if (isset($json['redes']))
            return $contact;

        #confirmar se existe algum erro
        if (isset($json['alert']))
            return $contact;

        #verifica se o id é valido
        if (!$id = parent::id($json['result'][1]))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $query = NULL;

        $client_saver = new OperationsBar($this->cli_config);

        $query = $client_saver->make_query();

        #confirma se o resultado é o esperado
        if (!isset($query['clientes']))
            return '{"errormess":"Foi impossivel gravar os dados de acesso do '. strtoupper (Clients::$group_name) .'. \\n\\n No entando alguns dados foram gravados. \\n\\n Por favor, recarregue o modulo, abra a ficha do '. Clients::$group_name .', atualize os dados e tente grava de novo. \\n \\n Se o erro continuar, entre em contato com a assistência técnica. ' . $this->exp_prefix . __LINE__ . '"}';

        if ($save_mode == "ADD" && !$identy)
        {

            try
            {
                $item_content = parent::make_call("spInsertClient", array($query['clientes'], $_POST['access_pass'], $_POST['access_nick'], $id));
            }
            catch (Exception $exp)
            {
                return parent::mess_alert($this->exp_prefix . __LINE__);
            }
        }

        if ($save_mode == "UPDATE" && $identy)
        {
            if ($json['result'][2] != Clients::$group_name)
                return '{"result":["",""]}';

            try
            {
                $item_content = parent::make_call("spUpdateClient", array($query['clientes'], $_POST['access_pass'], $_POST['access_nick'], $id));
            }
            catch (Exception $exp)
            {
                return parent::mess_alert($this->exp_prefix . __LINE__);
            }
        }

        if (!$ret = json_decode($item_content[0]['ret'], TRUE))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (isset($ret['mgp_error']))
        {
            $mess = $client_saver->db_error_message($ret['mgp_error']);
            if ($mess)
                return $mess;

            return '{"errormess":"Foi impossivel gravar os dados de acesso do '. strtoupper (Clients::$group_name) .'. \\n\\n No entando alguns dados foram gravados. \\n\\n Por favor, recarregue o modulo, abra a ficha do '. Clients::$group_name .', atualize os dados e tente grava de novo. \\n \\n Se o erro continuar, entre em contato com a assistência técnica. ' . $this->exp_prefix . __LINE__ . '"}';
        }

        if (isset($ret['opsat']) && $ret['opsat'] == "ok")
            return $contact;

        return '{"errormess":"Foi impossivel gravar os dados de acesso do '. strtoupper (Clients::$group_name) .'. \\n\\n No entando alguns dados foram gravados. \\n\\n Por favor, recarregue o modulo, abra a ficha do '. Clients::$group_name .', atualize os dados e tente grava de novo. \\n \\n Se o erro continuar, entre em contato com a assistência técnica. ' . $this->exp_prefix . __LINE__ . '"}';
    }

    /**
     * Apaga um colaborador na tabela na base de dados e passa a categoria desse contato na tabela contatos para "EX-Cliente".
     *
     * @param string $id_cli - valor da chave primária da tabela clientes.
     *
     * @return string - objeto json
     *
     */
    public function delete_client($id_cli)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$cli_id = parent::id($id_cli))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $result = parent::make_call("spDeleteClient", array($cli_id));

        if (isset($result[0]))
        {
            if (!$resp = json_decode($result[0]['ret'], TRUE))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            if (isset($resp['mgp_error']))
                return parent::mess_alert($this->exp_prefix . __LINE__);

            return '{"result":["' . $resp['folder'] . '","' . $resp['id'] . '"]}';
        }
        else
        {
            return parent::mess_alert($this->exp_prefix . __LINE__);
        }
    }

    /**
     * Sanatiza os inputs da tabela de clientes e valida a senha e o nick
     *
     * @uses Logex::$tcnx, Anti::validate_name
     *
     * @return boolean|string - False se não encotra erros ou mensagem de erros
     */
    private function validate_access($id_contact = "''")
    {

        $vlevel                 = (isset($_POST['access_level'])) ? filter_var($_POST['access_level'], FILTER_VALIDATE_INT) : "''";
        $_POST['public_status'] = (isset($_POST['public_status']) && $_POST['public_status'] === "online") ? "online" : "offline";

        if ($vlevel < 0 || $vlevel > 3)
            return '{"alert":"Não foi possivel guardar os dados de acesso. ' . $this->exp_prefix . __LINE__ . '"}';

        if (!empty($_POST['access_nick']) && !empty($_POST['access_pass']))
        {

            $_POST['access_nick'] = ($this->validate_name($_POST['access_nick'])) ? $this->validate_name($_POST['access_nick']) : NULL;
            $_POST['access_pass'] = ($this->validate_name($_POST['access_pass'])) ? $this->validate_name($_POST['access_pass']) : NULL;

            if (empty($_POST['access_pass']))
                return '{"errormess":"Senha inválida."}';

            if (empty($_POST['access_nick']))
                return '{"errormess":"Nick inválido."}';
        }

        return FALSE;
    }

}

?>
