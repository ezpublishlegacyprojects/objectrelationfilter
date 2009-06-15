<?php

class ObjectRelationFilter
{

   function ObjectRelationFilter()
   {

   }

   function createSqlParts( $params )
   {
       // first optional param element could be either 'or' or 'and', deafult is 'and'
       if( $params[0] === 'or' || $params[0] === 'OR' || $params[0] === 'and' || $params[0] === 'AND' )
       {
           $matchAll = ( strtolower( array_shift( $params ) ) === 'and' );
       }
       else
       {
           $matchAll = true;
       }

       // remaining params are pairs of attribute id and related object id which should be matched.
       // related object id can also be an array, witch results in a 'or' fetch on those relations
       $i = 0;
       $sqlCondArray = array();
       while( isset( $params[1] ) )
       {
           $attributeId = array_shift( $params );
           $relatedobjectId = array_shift( $params );

           if ( !is_numeric( $attributeId ) )
           {
               $tempAttributeId = eZContentObjectTreeNode::classAttributeIDByIdentifier( $attributeId );
               if ( $tempAttributeId === false )
               {
                   eZDebug::writeError( 'Unknown attribute identifier: '. $attributeId, 'ObjectRelationFilter::createSqlParts()' );
                   return array( 'tables' => '', 'joins' => '', 'columns' => '' );
               }
               $attributeId = $tempAttributeId;
           }

           if ( is_array( $relatedobjectId ) )
           {
               $toCondition = "l$i.to_contentobject_id IN ( " . join( ', ', $relatedobjectId ) . " )";
           }
           else if ( is_numeric( $relatedobjectId ) )
           {
               $toCondition = "l$i.to_contentobject_id= $relatedobjectId";
           }
           else
           {
                eZDebug::writeError( 'Unknown relation id: '. $relatedobjectId . ' url: ' . $_SERVER['REQUEST_URI'], 'ObjectRelationFilter::createSqlParts()' );
                continue;
           }

           $subSelect = "SELECT from_contentobject_id
               FROM ezcontentobject_link l$i
               WHERE l$i.from_contentobject_id = ezcontentobject_tree.contentobject_id AND
                     l$i.from_contentobject_version = ezcontentobject_tree.contentobject_version AND
                     l$i.contentclassattribute_id = $attributeId AND
                     $toCondition";

           $sqlCondArray[] = "ezcontentobject_tree.contentobject_id IN ( $subSelect )";
           $i++;
       }

       if ( isset( $sqlCondArray[0] ) )
       {
           $joins = '( ' . join( $matchAll ? ' AND ' : ' OR ', $sqlCondArray ) . ' ) AND';
       }
       else
       {
           $joins = '';
       }

       //eZDebug::writeDebug( $joins );
       return array( 'tables' => '', 'joins' => $joins, 'columns' => '' );
   }
}

?>