<?php
/**
 * script: Products.php
 * client: EPKyusho
 *
 * @version V3.50.130615
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

class Products extends Core
{

    private $product_config;

    private $exp_prefix = "PROD";

    public function __construct($JS)
    {

        parent::__construct();

        $this -> product_config = parent::json_file($JS);
    }

    public function get_product_config()
    {

        return $this -> product_config;
    }

    /**
     * Cria a ficha de apresentação de um produto. Esta função só aceita stored procedures que tenham como parametro apenas o identificador unico (id) do item e que este identificador seja um numero positivo.
     * 
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core::id()
     * @uses ItemSheet::make_all_sheet()
     * 
     * @param string $procedure - nome da stored procedure
     * @param string $id - idenficator único do item que possa ser convertido em um positivo inteiro pela função Core::id()
     * 
     * @return string em caso se sucesso uma strin HTML com a ficha do item ou string json com mensagem de erro
     */
    public function make_sheet($procedure,$id)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($_POST['toke']))
            return parent::mess_alert($this->exp_prefix . __LINE__);
        try
        {
            $sheet = new ItemSheet();
            return $sheet -> make_all_sheet($procedure, $this -> product_config, array($id));
        }
        catch(Exception $exp)
        {
            return parent::mess_alert($exp -> getMessage(), $exp -> getCode());
        }
    }

    /**
     * Cria um formulário para edição de um produto. Esta função só aceita stored procedures que tenham como parametro apenas o identificador unico (id) do item e que este identificador seja um numero positivo.
     * 
     * @uses Logex::check()
     * @uses Core::mess_alert()
     * @uses Core::id()
     * @uses OperationsBar::edit_item_sheet_db()
     * 
     * @param string $procedure - nome da stored procedure
     * @param string $id - idenficator único do item que possa ser convertido em um positivo inteiro pela função Core::id()
     * 
     * @return string em caso se sucesso uma string HTML com a formulário para edição do item ou string json com mensagem de erro
     */
    public function edit_item($procedure, $id)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($_POST['toke']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $addedit = new OperationsBar($this -> product_config);
        $addedit -> set_mode("UPDATE");
        return $addedit -> edit_item_sheet_db($procedure, array($id));
    }

    /**
     *
     */
    public function clone_item($id)
    {
        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($_POST['toke']))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        $addedit = new OperationsBar($this -> product_config);
        $addedit -> set_mode("CLONE");
        return $addedit -> edit_item_sheet_db("spProductData",array($id));
    }

    /**
     * Estatisticas dos contatos
     *
     * @uses Logex::$tcnx, Core::mess_alert();
     *
     * @return obejct json
     */
    public function stats()
    {

        if (!parent::check())
            return FALSE;

        $charts = array(
            "Publico",
            "Classes",
            "Paises",
            "Cidades",
            "Empresa areas de atividade"
        );
        $count_charts = 0;

        $query = "SELECT  'homem' AS publico, COUNT(homem) AS c_cat
                    FROM produtos
                    WHERE homem =1
                    UNION ALL
                    SELECT  'mulher' AS publico, COUNT(mulher) AS c_cat
                    FROM produtos
                    WHERE mulher =1
                    UNION ALL
                    SELECT  'criança' AS publico, COUNT(crianca) AS c_cat
                    FROM produtos
                    WHERE crianca =1 ORDER BY c_cat DESC;";
        $query .= "SELECT categoria, count(categoria) as c_cat FROM produtos GROUP BY categoria ORDER BY c_cat DESC;";
        /* $query .= "SELECT pais, count(pais) as c_cat FROM contactos GROUP BY pais ORDER BY c_cat DESC;";
         $query .= "SELECT cidade, count(cidade) as c_cat FROM contactos GROUP BY cidade ORDER BY c_cat DESC;";
         $query .= "SELECT ramo_actividade, count(ramo_actividade) as c_cat FROM contactos WHERE categoria='empresa' GROUP BY ramo_actividade ORDER BY c_cat DESC";*/

        if (Logex::$tcnx -> multi_query($query))
        {
            do
            {
                $data = NULL;

                if ($r_stat = Logex::$tcnx -> store_result())
                {
                    while ($stats = $r_stat -> fetch_array())
                    {
                        $data .= '"' . $stats[0] . '":"' . $stats[1] . '",';
                    }

                    $data = trim($data, ",");
                    $chart .= '"' . $charts[$count_charts] . '":{"type":"pie","data":{' . $data . '}},';

                    $count_charts++;

                    $r_stat -> free();
                }

            }
            while(Logex::$tcnx->next_result());
        }
        else
        {
            return parent::mess_alert("CONT" . __LINE__);
        }

        $rtr_stats = '{' . trim($chart, ",") . '}';

        return $rtr_stats;

    }

    /**
     * Notas relativas a um item
     * 
     * @param string $item_id - identificador único do item
     * 
     * @return string json
     * 
     */
    public function show_notes($item_id)
    {

        if (!parent::check())
            return parent::mess_alert($this->exp_prefix . __LINE__);

        if (!$id = parent::id($item_id))
            return parent::mess_alert($this->exp_prefix . __LINE__);

        try
        {
            $notes = new GestNotes();
            return $notes -> show_notes($this -> product_config['notes'], $id);            
        } 
        catch (Exception $ex) 
        {
            return parent::mess_alert($ex->getMessage());
        }
    }

    /**
     *
     */
    public function product_for_module($id_contact, $ret_type = NULL)
    {

        if (!parent::check())
            return parent::mess_alert($this -> exp_prefix . __LINE__);

        if(!$id = parent::id($id_contact))
            return parent::mess_alert($this -> exp_prefix . __LINE__);

        Logex::$tcnx->real_query("call spProductData($id)");

        if(!$rprod = Logex::$tcnx -> store_result())
            return parent::mess_alert($this -> exp_prefix . __LINE__);

        if(!$prod = $rprod-> fetch_assoc())
            return parent::mess_alert($this -> exp_prefix . __LINE__);

        $rprod -> free_result();

        Logex::$tcnx -> next_result();

        $type = $this->product_config['name'];

        try
        {
            $o_img = new GestImage();
            $img = $o_img -> send_images_json($prod[$this -> product_config['admin']['private']['icon']], "src", FALSE, 0);
        }
        catch(Exception $exp)
        {
            $img = "imagens/sem_photo.png";
        }

        $name = $prod["produtos.referencia"];
        $id = $prod["produtos.id_produto"];

        $cont = NULL;

        if ($ret_type == "SHEET")
        {
            $cont = <<<EOF
         <div class="filehalfdiv"  >
             <div class="filedivimg" data-id='{$id}'>
               <img src="{$img}" data-id="{$id}" class="modimg">
               <p>{$name}</p>
             </div>
         </div>
EOF;
        }
        else
        {
            $cont = <<<EOF
         <div class="dvB terco">
            <img class="ig15A" data-action="delthis" src="imagens/minidel.png" draggable="false">
            <div class="wterco">
                <img src="{$img}" class="modimg">
                <p>{$name}</p>
                <input type="hidden" name="mod_{$type}_id[{$id}]" value="{$id}">
            </div>
         </div>
EOF;
        }
        return $cont;
    }

}
?>
