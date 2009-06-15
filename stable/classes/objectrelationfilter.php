<?php
class ObjectRelationFilter
{
	function ObjectRelationFilter()
	{
	}

	function createSqlParts($params)
	{
		// first optional param element should be either 'or' or 'and'
		if(!is_numeric($params[0]))
			$clause = array_shift($params);
		else
			$clause = "and";

		// remaining params are pairs of attribute id and object id which should be matched.
		// object id can also be an array of object ids, in that case the match is on either object id.
		$t = 0;
		$sqlCond="";
		$sqlJoins="";
		while(sizeof($params) > 1) {
			$attribute_id = array_shift($params);
			$relatedobject_id = array_shift($params);
			if (!is_numeric($relatedobject_id) ){
				$relatedobject_id=0;
			}
			//echo $attribute_id."<br>";
            if ( !is_numeric( $attribute_id ) )
            	$attribute_id = eZContentObjectTreeNode::classAttributeIDByIdentifier( $attribute_id );
			if ( $attribute_id === false )
	            eZDebug::writeError( "Unknown attribute identifier", "objectrelationfilter::createSqlParts()" );
			$sqlCond = " ( contentclassattribute_id=$attribute_id and to_contentobject_id=$relatedobject_id) ";
			if($t >= 1)
				$sqlJoins .= $clause .$sqlCond ;
			else
				$sqlJoins .= $sqlCond ;
			$t++;
		}
		$db =& eZDB::instance();
		$result=$db->arrayQuery("select distinct from_contentobject_id from ezcontentobject_link where $sqlJoins");
		unset($db);
		$liste=Array();
	 	foreach ( $result as $row )
	  	{
     		$liste[]=$row['from_contentobject_id'];
  	  	}
  	  	unset($result);
  	  
  	  	if (count($liste) ==0) 
  	  		$liste[]=0;
		$sqlJoins=" ezcontentobject.id in(".implode(",",$liste).") and ";
		return array('tables' => '', 'joins'  => $sqlJoins);
	}
}
?>
