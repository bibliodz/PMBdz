<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmb_h2o.inc.php,v 1.4 2014-01-14 14:22:59 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");
require_once($include_path."/h2o/h2o.php");

class pmb_StringFilters extends FilterCollection {
	
	function limitstring($string, $max = 50, $ends = "[...]"){
		if(strlen($string)> $max){
			$string = substr($string,0,($max - strlen($ends))).$ends;
		}
		return $string;
	}
}


class Sqlvalue_Tag extends H2o_Node{
	private $struct_name;
	
	
	function __construct($argstring, $parser, $position){
		$this->struct_name = $argstring;
		$this->pmb_query = $parser->parse('endsqlvalue');
	}
	
	function render($context,$stream){
		global $dbh;
		
		$query_stream = new StreamWriter;
		$this->pmb_query->render($context, $query_stream);
		$query = $query_stream->close();
		$result = mysql_query($query,$dbh);
		
		if(mysql_num_rows($result)){
			$struct =array();
			while ($row = mysql_fetch_assoc($result)){
				$struct[]=$row;
			}
			$context->set($this->struct_name,$struct);
		}
	}
}

class Sparqlvalue_Tag extends H2o_Node{
	private $struct_name;
	private $endpoint;

	function __construct($argstring, $parser, $position){
		$params = explode(" ",$argstring);
		$this->struct_name = $params[0];
		$this->endpoint = $params[1];
		$this->sparql_query = $parser->parse('endsparqlvalue');
	}

	function render($context,$stream){
		global $dbh;
		global $class_path;

		$query_stream = new StreamWriter;
		$this->sparql_query->render($context, $query_stream);
		$query = $query_stream->close();
		
		require_once ("$class_path/rdf/arc2/ARC2.php");
		$config = array(
			'remote_store_endpoint' => $this->endpoint,
			'remote_store_timeout' => 10
		);
		$store = ARC2::getRemoteStore($config);
		$context->set($this->struct_name,$store->query($query,'rows'));
	}
}

h2o::addTag(array("sqlvalue"));
h2o::addTag(array("sparqlvalue"));
h2o::addFilter(array('pmb_StringFilters'));