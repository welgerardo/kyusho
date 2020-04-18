<?php
/**
 * script: Pages.php
 * client: EPKyusho
 *
 * @version V3.00.110615
 *
 * @author Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 * @author mgpdinamic.com <comercial@mgpdinamic.com>
 *
 * @license Todos os direitos reservados / All rights reserved
 *
 * @copyright Manuel Gerardo Pereira <gpereira@mgpdinamic.com>
 *
 */

ini_set('display_errors', 0);
require 'Core.php';
/**
 * Esta classe manipula páginas individuais, ou seja, páginas que não fazem parte de um conjunto de páginas.
 * Cada página destas é representado por um sub-modulo, que essencialmente é um objeto de segundo nivel não obrigatorio
 * dento do objeto JHOMEPAGE do arquivo Master.json.
 * Este sub-modulos tem objetos que repelicam os objetos exteriores. São eles midia, link, export, search.
 * Objetos com estes nomes dentro do primeiro nivel do sub-modulo tem obdecer ás mesmas regras dos objetos exteriores com o mesmo nome
 *
 */
class Pages extends Core
{
    private $config;
    private $exp_prefix = "PGA";

    public function __construct($JS)
    {
        parent::__construct();

        $this -> config = $this -> json_file($JS);
    }
    /**
     * 
     * @return json objeto de configuração do item
     */
    public function get_config()
    {
        return $this -> config;
    }

    /**
     * Retira os dados de um sub-modulo da base de dados
     *
     * @uses Core::id()
     * @uses Cote::make_call()
     * 
     * @param string $procedure - nome da stored procedure para retirar os dados do item da base de dados
     * @param string $item_id - identificador do item na base de dados. Pode ser um numero ou um nome
     *
     * @return null|array com os dados do sub-modulo
     *
     */
    private function get_item($procedure,$item_id)
    {
        $id = parent::id($item_id);
        
        if(!$id)
            return NULL;

         try
        {
            $result  = parent::make_call($procedure, array($id));
            
            $hp = (isset($result[0])) ? $result[0] : NULL;
        } 
        catch (Exception $ex)
        {
            return NULL;
        }     

        return $hp;

    }

    /**
     * Atualiza os dados de um item na base de dados
     * 
     * @uses OperatinosBar::edit_item_sheet()
     * @uses Core::mess_alert()
     * @uses Core::check()
     * @uses Pages::get_item() 
     * @uses Pages::configurations()
     * 
     * @param string $prodecure - nome da stored procedure que atualiza o item
     * @param string $item_id - identificador único do item. Pode ser um numero ou um nome
     * @param string $module - nome da página na base de dados
     * 
     * @return string Html com um formulário.
     */
    public function edit($prodecure,$item_id, $module)
    {
        if (parent::check())
            parent::mess_alert("PG" . __LINE__);

        $hp = $this -> get_item($prodecure,$item_id);
        $identifier = $hp[$this -> config['admin']['private']['identifier']['db']];

        $config = $this -> configurations($module,$identifier);

        $edit_sheet = new OperationsBar($config);
        $edit_sheet->set_mode("UPDATE");
        return $edit_sheet -> edit_item_sheet($hp);
    }

    /**
     * Adapta o sub-modulo ás regras de configuração dos objetos para serem utlizados pelas classes do arquivo Core.php
     * 
     * 
     * @param string $module - nome do objeto pai
     * @param string $object_name - nome do objeto sub-modulo de configuração
     *
     * @return false|array associativa
     *
     */
    private function configurations($module,$object_name)
    {
        $page = strtolower($module);

        if (!isset($this -> config[$page][$object_name]))
            return FALSE;

        $item['table'] = $this -> config['table'];
        $item['name'] = $this -> config['name'];
        $item['submenu'] = $this -> config['submenu'];
        $item['notes'] = $this -> config['notes'];

        $item['admin']['private'] = $this -> config['admin']['private'];
        $item['admin']['public'] = $this -> config['admin']['public'];

        $item['content'] = $this -> config[$page][$object_name];
        $item['midia'] = NULL;
        $item['export'] = NULL;
        $item['search'] = NULL;

        if (isset($item['content']['midia']))
        {
            $item['midia']['midia'] = $item['content']['midia'];
            unset($item['content']['midia']);
        }

        if (isset($item['content']['search']))
        {
            $item['search'] = $item['content']['search'];
            unset($item['content']['search']);
        }

        if (isset($item['content']['export']))
        {
            $item['export'] = $item['content']['export'];
            unset($item['content']['export']);
        }

        return $item;
    }
    
    /**
     * Salva os dados da atualização de um item na base de dados
     * 
     * @uses Core::check()
     * @uses Core::mess_alert() 
     * @uses Pages::get_item() 
     * @uses Pages::configurations()
     * @uses OperationsBar::set_mode()
     * @uses OperationsBar::save_item()
     * 
     * @param string $save_prodedure - nome da stored procedure para gravar uma atualização na base de dados
     * @param string $data_procedure - nome da stored procedure para extrair os dados de um item na base de dados
     * @param string $toke - identificador único de um item. Pode ser numero ou nome
     * @param string $module - nome da página na base de dados
     * 
     * @return json com dados do item guardado ou mensagem de erro
     * 
     */
    public function save($save_prodedure,$data_procedure,$toke,$module)
    {
        if (parent::check())
            parent::mess_alert($this->exp_prefix . __LINE__);

        $hp = $this -> get_item($data_procedure,$toke);
        $identifier = $hp[$this -> config['admin']['private']['identifier']['db']];

        $config = $this -> configurations($module,$identifier);

        $edit_sheet = new OperationsBar($config);
        $edit_sheet->set_mode("UPDATE");
        return $edit_sheet -> save_item($save_prodedure,NULL);
    }

    /**
     * Cria a ficha de apresentaçõa de um sub-modulo. O parametro status permite alterar os estado dos sub-modulos. Por defeito estão todos offline
     *
     * @uses Logex::check()
     * @uses Core::id()
     * @uses Core::mess_alert()
     * @uses Pages::get_item()
     * @uses Pages::configurations()
     * @uses ItemSheet::make_sheet_content()
     * @uses ItemSheet::make_sheet()
     *
     * @param string $procedure - nome da store procedure para retirar os dados
     * @param string $item_id - valor da chave primária na tabela da base de dados 
     * @param string $module - nome da página na base de dados
     * @param boolean $status - estado por omissão do sub-modulo. Se TRUE o sub-modulo é apresentado como online
     *
     * @return string HTML em caso de sucesso ou JSON alert em caso de erros
     *
     */
    public function make_sheet_i($procedure, $item_id, $module,$status=FALSE)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        //retira os valores do item da base de dados
        if (!$hp = $this -> get_item($procedure,$item_id))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        //configura o item
        if (!$configu = $this -> configurations($module,$hp[$this -> config['admin']['private']['identifier']['db']]))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        //coloca todos os itens online, uma vez que este modulo não permite guardar itens offline
        if($status)
        {
            $configu['admin']['public']['status']['db'] = "estado";
            $hp['estado'] = "online";
        }
        
        $mid = NULL;

        $sheet = new ItemSheet();

        if (!empty($configu["midia"]))
            $mid .= $sheet -> make_sheet_content($configu["midia"], $hp);

        if (isset($configu['content']) && is_array($configu['content']))
        {
            foreach ($configu['content'] as $key => $value)
            {
                $sheet_content = NULL;
                $flag = NULL;
                
                foreach ($value as &$valtorepl){
                    
                    if(!isset($valtorepl['type']))
                        continue;
                    
                    $patt[] = "/ISEO/";
                    $patt[] = "/TSEO/";
                    
                    $repl[] = "INPUT";
                    $repl[] = "TEXTAREA";
                    
                    $valtorepl['type'] = preg_replace($patt,$repl, $valtorepl['type']);
                }
                

                $shp["baia"][$key] = $value;
                $sheet_content = $sheet -> make_sheet_content($shp["baia"], $hp, TRUE);
                unset($shp["baia"][$key]);

                if (in_array($key, $GLOBALS['LANGPAGES']))
                    $flag = " <span class='fileboxflag'><img src='" . $GLOBALS['LANGFLAGS'][$key] . "' class='ig10M'></span> ";

                $mid .= "
                    <div class='wlang'>
                        $flag
                        $sheet_content
                    </div>";
            }
        }

        try
        {
            return $sheet -> make_sheet($configu, $hp, $mid);
        }
        catch(Exception $exp)
        {

            return parent::mess_alert($exp->getMessage());
        }
    }

}
?>
