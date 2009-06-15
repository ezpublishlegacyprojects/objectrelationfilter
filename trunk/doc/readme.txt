Change by stephane couzinier 03/12/2006
Add ObjectRelationFilterAndInv
return reverse related objects and related objects link with an object.
exemple:
You have 1 object ID, you  want all nodes "link" with  this object:
{def   $related=fetch('content',
      'tree',
      hash(
        'parent_node_id', 2,
        'class_filter_type', 'include',
        'class_filter_array', array('MY CLASS1','MY CLASS2'),
        'extended_attribute_filter', hash(
           'id', 'ObjectRelationFilterAndInv',
           'params', array('or',
              MY_CLASS1_OBJECTRELATION_ATTRIBUTE_ID, OBJECTID,
			  MY_CLASS2_OBJECTRELATION_ATTRIBUTE_ID, OBJECTID,
 			  OBJECTID_OBJECTRELATION_ATTRIBUTE_ID, OBJECTID
           ))
		   ))
}


Change by stephane couzinier 25/11/2006
Correct bug with 'or' cond.
Add 'ObjectRelationExist':
return nodes when there is something in relation object
sample:
  'extended_attribute_filter', hash(
		           'id', 'ObjectRelationExist',
		           'params', array(490)),
return node when there is data in the attribute 490


ObjectRelationFilter
====================

This extended_attribute_filter is very useful for fetching reverse related objects while making use of filtering and sorting options that the fetch(content, reverse_related_objects) function doesn't provide.
It is used in combination with fetch(content, list) or fetch(content, tree). It filters nodes based on their relation with other objects, either by attribute level relation or by object level relation.

As it is a extended_attribute_filter, the syntax follows the general syntax of that filter:

fetch('content',
      'list'|'tree',
      hash(
        'parent_node_id', $id,
        'extended_attribute_filter', hash('id', 'ObjectRelationFilter', 'params', $params),
        ...
			)
}

$params is an array with the parameters for the ObjectRelationFilter:

$params = array( ['or'|'and',] $attribute_id, $object_id [, $attribute_id, $object_id] ... )

The first element is either 'or' or 'and' or it can be omitted. In that case 'and' is assumed.
It serves as the glue logic between the subsequent attribute/object id pairs.
The attribute_id is the numerical attribute id of the object class we want to filter. This attribute has to be of type ezobjectrelation or ezobjectrelationlist.
The object_id can be a integer or an array of integers. In the first case the match is made on the relation with that object, the latter case will match on any object id contained in the array.
Multiple attribute_id/object_id pairs can be specified. They will be combined with AND or OR logic, depending on the first argument.
The fetch is executed as a single SQL query, and therefore very eficient.

Examples:
========

Imagine the following content tree:

root [node_id: 1, obj_id: 10]
  |
  +- articles [node_id: 2, obj_id: 20]
  |    |
  |    +- 2006 [node_id: 5, obj_id: 50]
  |    |    |
  |    |    +- Winter Olympics [node_id: 10, obj_id: 101]
  |    |    :
  |    |    +- [other articles]
  |    |
  |    +- 2005 [node_id: 6, obj_id: 60]
  |         |
  |         +- Tsunami [node_id: 11, obj_id: 102]
  |         :
  |         +- [other articles]
  |
  +- regions [node_id: 3, obj_id: 30]
  |    |
  |    +- asia [node_id: 7, obj_id: 70]
  |    |
  |    +- europe [node_id: 8, obj_id: 80]
  |    :
  |    +- [other regions]
  |
	+- sections [node_id: 4, obj_id: 40]
       |
       +- news [node_id: 9, obj_id: 90]
       |
       +- sport [node_id: 10, obj_id: 100]
       :
       +- [other sections]

Assume the nodes 'Winter Olympics' and 'Tsunami' are of class Article, and all other nodes are Folders.
Assume also the Article class has two attributes of type ezobjectrelationlist, with attribute ids 200 and 201, the first stores related regions, the second related sections.
When we want to fetch all articles related to news in Asia, we do this:

fetch('content',
      'tree',
      hash(
        'parent_node_id', 2,
        'class_filter_type', 'include',
        'class_filter_array', array('article'),
        'extended_attribute_filter', hash(
           'id', 'ObjectRelationFilter',
           'params', array('and',
              200, 70,
              201, 90,
           )
        )
        [other options like sort_by, offset, limit etc.]
			)
}

To fetch all articles from 2005 about any topic in either Asia or Europe:

fetch('content',
      'list',
      hash(
        'parent_node_id', 6,
        'class_filter_type', 'include',
        'class_filter_array', array('article'),
        'extended_attribute_filter', hash(
           'id', 'ObjectRelationFilter',
           'params', array(
              200, array(70, 80)
           )
        )
        [other options like sort_by, offset, limit etc.]
			)
}


----------------------
Marc Boon - March 2006
marcboon[AT]dds[DOT]nl