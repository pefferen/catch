<?php
class AdlibSearchQuery{

  /*
   * Todo items here.
   */
  // members
  /**
  *  parameters are the query parameters that are sent to the adlib server
  */
  private $parameters;

  /**
   *  fields are the fields we want to receive from the adlibserver
   */
  private $fields;

  /**
   *  allowed XML types are: 'grouped', 'unstructured'
   */
  private $xmltype;

  /**
   * maximum number of results to return
   */
  private $limit;

  /**
   * number of the first result to return
   */
  private $startFrom;

  // methods
  /**
  * Constructor
  */
  function __construct(){
    $this->parameters = array();
    $this->fields = array();
    // set default xml type
    $this->xmltype="grouped";
  }

  /**
   * TODO: parameters are not that simple, you can OR or AND and add multiple parameters for the same field
   * @param string $name
   * @param string $value
   * @param string $compare
   * @param string $booleanop
   */
  function addParameter($name, $value="", $compare="", $booleanop=""){
    $this->parameters[]=array("name"=>$name, "value"=>$value, "compare"=>$compare ,"booleanop"=>$booleanop);
  }

  /**
   * Add one field to the query, the query expectes a comma seperated string of fields
   * @param string $fieldname
   */
  function addField($fieldname){
    if(!in_array($fieldname, $this->fields)){
      $this->fields[]=$fieldname;
    }
  }

  /**
   * Add array of fields to the fields array
   * @param array $fieldArray
   */
  function addFields($fieldArray){
    foreach($fieldArray as $fieldname){
      $this->addField($fieldname);
    }
  }

  /**
   * set limit: maximum amount of keys to return
   * @param integer $limit
   */
  function setLimit($limit){
    $this->limit = $limit;
  }

  /**
   * set startFrom: first key number to return in the result
   * @param integer $start
   */
  function startFrom($start){
    $this->startFrom = $start;
  }


  /**
   * Set XML type
   * @param string $xmltype
   */
  public function setXMLtype($xmltype) {
    $this->xmltype = $xmltype;
  }


  /**
   * Get the complete query
   * @return string
   */
  function getQueryItems(){
    $params= $this->_parameters2String();
    $items=$params;
    // add fields
    if(count($this->fields)>0){
      $fields = "&fields=".rawurlencode(implode(",", $this->fields));
      $items.=$fields;
    }
    // addd limit
    if(isset($this->limit)){
      $items.="&limit=".$this->limit;
    }

    // add the number of the first record to return
    if(isset($this->startFrom)){
      $items.="&startfrom=".$this->startFrom;
    }

    // add XMLtype
    $items.='&xmltype='.$this->xmltype;
    return $items;
  }

  /**
   * helper function to convert the array with parameters to a string for the query
   * @return string the parameter array as one string
   */
  function _parameters2String(){
    $isFirst=true;
    $paramStr="";
    foreach($this->parameters as $param){
      /* There are 3 type of parameters:
       * 	1) <name> <operator> <value> e.g. modification > '1970-01-01'
       * 	2) <name> <value> 			 e.g. sort 'edit.date'
       * 	3) <name>					 e.g. creator->all (linked search)
       */
      // case 1
      if(!empty($param['value']) && !empty($param['compare'])){
        if(!$isFirst){
          if(!empty($param['booleanop'])){
            $paramStr .= "%20".rawurlencode($param['booleanop'])."%20";
          }
        }
        // only clauses of case 1 are joined by boolean operators
        $paramStr .= rawurlencode($param['name']).$param['compare']."'".rawurlencode($param['value'])."'"."%20";

      }
      // case 2
      elseif(!empty($param['value']) && empty($param['compare'])){
        $paramStr .= rawurlencode($param['name'])."%20'".$param['value']."'";
      } // case 3
      elseif(empty($param['value']) && empty($param['compare'])) {
        $paramStr .= rawurlencode($param['name'])."%20";
      }
      $isFirst=false;
    }
    return $paramStr;
  }
}