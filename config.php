<?php
global $argc;
global $argv;
$config['argv'] = ($argc > 0) ? $argv : null;
$config['site']['name']   = 'Data Gov.ie';          /*Name of your site. Appears in page title, address etc. */
$config['site']['server'] = 'govdata.ie';           /* 'site' in http://site */
$config['site']['path']   = '';                     /* '/foo' in http://site/foo */
$config['site']['theme']  = 'cso';                  /* 'default' in /var/www/site/theme/cso */
$config['site']['logo']   = 'logo_data-gov.ie.png'; /* 'logo.png' in /var/www/site/theme/default/images/logo.png */

$config['server']['geo.govdata.ie']   = 'geo.govdata.ie';
$config['server']['stats.govdata.ie'] = 'stats.govdata.ie';

/*
 * Common prefixes for this dataset
 */
$config['prefixes'] = array(
    'rdf'               => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
    'rdfs'              => 'http://www.w3.org/2000/01/rdf-schema#',
    'owl'               => 'http://www.w3.org/2002/07/owl#',
    'xsd'               => 'http://www.w3.org/2001/XMLSchema#',
    'dcterms'           => 'http://purl.org/dc/terms/',
    'foaf'              => 'http://xmlns.com/foaf/0.1/',
    'skos'              => 'http://www.w3.org/2004/02/skos/core#',
    'wgs'               => 'http://www.w3.org/2003/01/geo/wgs84_pos#',
    'dcat'              => 'http://www.w3.org/ns/dcat#',

    'sdmx'              => 'http://purl.org/linked-data/sdmx#',
    'sdmx-attribute'    => 'http://purl.org/linked-data/sdmx/2009/attribute#',
    'sdmx-code'         => 'http://purl.org/linked-data/sdmx/2009/code#',
    'sdmx-concept'      => 'http://purl.org/linked-data/sdmx/2009/concept#',
    'sdmx-dimension'    => 'http://purl.org/linked-data/sdmx/2009/dimension#',
    'sdmx-measure'      => 'http://purl.org/linked-data/sdmx/2009/measure#',
    'sdmx-metadata'     => 'http://purl.org/linked-data/sdmx/2009/metadata#',
    'sdmx-subject'      => 'http://purl.org/linked-data/sdmx/2009/subject#',
    'qb'                => 'http://purl.org/linked-data/cube#',

    'year'         => 'http://reference.data.gov.uk/id/year/',

    'statsDataGov' => 'http://stats.govdata.ie/',
    'concept'      => 'http://stats.govdata.ie/concept/',
    'codelist'     => 'http://stats.govdata.ie/codelist/',
    'dsd'          => 'http://stats.govdata.ie/dsd/',
    'property'     => 'http://stats.govdata.ie/property/',
    'geoDataGov'   => 'http://geo.govdata.ie/',
    'DataGov'      => 'http://govdata.ie/',

    'sch-ont' => 'http://education.data.gov.uk/ontology/school#',

    'afn' => 'http://jena.hpl.hp.com/ARQ/function#'
);

/**
 * SPARQL Queries
 */
/* Empty query (temporary) */
$config['sparql_query']['empty'] = '';

/**
 * Default query is DESCRIBE
 * '<URI>' value is auto-assigned from current request URI
 */
$config['sparql_query']['default'] = "
    DESCRIBE <URI>
";
/**
 * Entity Set
 */
/* URI path for this entity.*/
$config['entity']['default']['path']     = '';
/* SPARQL query to use for this entity e.g., $config['sparql_query']['default'] */
$config['entity']['default']['query']    = 'default';
/* HTML template to use for this entity */
$config['entity']['default']['template'] = 'page.default.html';


/**
 * Entity sets can be configured here:
 */
$config['sparql_query']['cso_home'] = "
CONSTRUCT {
    ?city a geoDataGov:City .
    ?city a skos:Concept .
    ?city skos:prefLabel ?cityLabel .

    ?province a geoDataGov:Province .
    ?province a skos:Concept .
    ?province skos:prefLabel ?provinceLabel .
}
WHERE {
    ?city a geoDataGov:City .
    ?city a skos:Concept .
    ?city skos:prefLabel ?cityLabel .

    ?province a geoDataGov:Province .
    ?province a skos:Concept .
    ?province skos:prefLabel ?provinceLabel .
}
";

/* URI path for this entity */
$config['entity']['cso_home']['path']     = "/";
/* SPARQL query to use for this entity e.g., $config['sparql_query']['cso_home'] */
$config['entity']['cso_home']['query']    = 'cso_home';
/* HTML template to use for this entity */
$config['entity']['cso_home']['template'] = 'page.home.html';

$config['entity']['cso_about']['path']     = "/about";
$config['entity']['cso_about']['query']    = 'empty';
$config['entity']['cso_about']['template'] = 'page.about.html';

$config['entity']['cso_data']['path']     = '/data';
$config['entity']['cso_data']['query']    = 'default';
$config['entity']['cso_data']['template'] = 'page.default.html';

$config['entity']['cso_codelist']['path']     = '/codelist';
$config['entity']['cso_codelist']['query']    = 'default';
$config['entity']['cso_codelist']['template'] = 'page.default.html';


$config['sparql_query']['cso_geoArea'] = "
CONSTRUCT {
    ?s ?p ?o .
    ?o a skos:Concept .
    ?o skos:prefLabel ?o_prefLabel .
    ?o rdfs:label ?label .

    <URI> ?p0 ?o0 .
}
WHERE {
    {
        ?s ?geoArea <URI> .
        ?s ?p ?o .
        OPTIONAL {
            ?o a skos:Concept .
            ?o skos:prefLabel ?o_prefLabel .
        }
        OPTIONAL {
            ?o rdfs:label ?label .
        }
    }
    UNION
    {
        <URI> ?p0 ?o0 .
    }
}
";


$config['entity']['cso_geoArea']['path']     = '/city';
$config['entity']['cso_geoArea']['query']    = 'cso_geoArea';
$config['entity']['cso_geoArea']['template'] = 'page.geo.html';


$config['entity']['cso_province']['path']     = '/province';
$config['entity']['cso_province']['query']    = 'cso_geoArea';
$config['entity']['cso_province']['template'] = 'page.geo.html';


$config['entity']['doe_school']['path']     = '/school';
$config['entity']['doe_school']['query']    = 'doe_school';
$config['entity']['doe_school']['template'] = 'page.school.html';
$config['sparql_query']['doe_school'] = "
CONSTRUCT {
    <URI>
        ?p ?o ;
        sch-ont:address
            ?address1, ?address2, ?address3 .
}

WHERE {
    <URI>
        a sch-ont:School ;
        ?p ?o .

    OPTIONAL { <URI> sch-ont:address [ sch-ont:address1 ?address1 ] . }
    OPTIONAL { <URI> sch-ont:address [ sch-ont:address2 ?address2 ] . }
    OPTIONAL { <URI> sch-ont:address [ sch-ont:address3 ?address3 ] . }
}
";



$config['sparql_query']['dsd'] = "
CONSTRUCT {
    ?dataset qb:structure ?dsd .
    ?dataset sdmx-metadata:title ?sdmxMetadataTitle .
    ?dsd a qb:DataStructureDefinition .

    ?dsd qb:dimension ?dimensionProperty .
    ?dimensionProperty rdfs:label ?dimensionPropertyLabel .
    ?dimensionProperty qb:concept ?dimensionConcept .
    ?dimensionProperty rdfs:range ?dimensionPropertyRange .
    ?dimensionConcept rdfs:label ?dimensionConceptLabel .

    ?dsd qb:measure ?measureProperty .
    ?measureProperty rdfs:label ?measurePropertyLabel .
    ?measureProperty qb:concept ?measureConcept .
    ?measureProperty rdfs:range ?measurePropertyRange .
    ?measureConcept rdfs:label ?measureConceptLabel .

}
WHERE {
    ?dataset qb:structure ?dsd .
    ?dataset sdmx-metadata:title ?sdmxMetadataTitle .
    ?dsd a qb:DataStructureDefinition .
    ?dsd qb:component ?component .

    OPTIONAL {
        ?component qb:dimension ?dimensionProperty .
        ?dimensionProperty rdfs:label ?dimensionPropertyLabel .
        ?dimensionProperty qb:concept ?dimensionConcept .
        OPTIONAL {
            ?dimensionProperty rdfs:range ?dimensionPropertyRange .
        }
        {
            ?dimensionConcept rdfs:label ?dimensionConceptLabel .
        }
        UNION
        {
            ?dimensionConcept skos:prefLabel ?dimensionConceptLabel .
        }
    }
    OPTIONAL {
        ?component qb:measure ?measureProperty .
        ?measureProperty rdfs:label ?measurePropertyLabel .
        ?measureProperty qb:concept ?measureConcept .
        OPTIONAL {
            ?measureProperty rdfs:range ?measurePropertyRange .
        }
        {
            ?measureConcept rdfs:label ?measureConceptLabel .
        }
        UNION
        {
            ?measureConcept skos:prefLabel ?measureConceptLabel .
        }
    }
}
";
$config['entity']['dsd']['path']     = '/dsd';
$config['entity']['dsd']['query']    = 'dsd';
$config['entity']['dsd']['template'] = 'page.dsd.html';


$config['entity']['dsd_persons-by-birthplace']['path']     = '/dsd/persons-by-birthplace';
$config['entity']['dsd_persons-by-birthplace']['query']    = 'dsd';
$config['entity']['dsd_persons-by-birthplace']['template'] = 'page.dsd.html';


$config['sparql_query']['cso_class'] = "
CONSTRUCT {
    <URI> ?p1 ?o1 .

    ?s2 a <URI> .
    ?s2 skos:prefLabel ?o3 .
}
WHERE {
    {
     <URI> ?p1 ?o1 .
    }
    UNION
    {
        ?s2 a <URI> .
        OPTIONAL {
           ?s2 skos:prefLabel ?o3 .
        }
    }
}
";
$config['entity']['cso_class_administrative-county']['path']     = '/AdministrativeCounty';
$config['entity']['cso_class_administrative-county']['query']    = 'cso_class';
$config['entity']['cso_class_administrative-county']['template'] = 'page.class.html';

$config['entity']['cso_class_city']['path']     = '/City';
$config['entity']['cso_class_city']['query']    = 'cso_class';
$config['entity']['cso_class_city']['template'] = 'page.class.html';

$config['entity']['cso_class_electoral-division']['path']     = '/ElectoralDivision';
$config['entity']['cso_class_electoral-division']['query']    = 'cso_class';
$config['entity']['cso_class_electoral-division']['template'] = 'page.class.html';

$config['entity']['cso_class_enumeration-area']['path']     = '/EnumerationArea';
$config['entity']['cso_class_enumeration-area']['query']    = 'cso_class';
$config['entity']['cso_class_enumeration-area']['template'] = 'page.class.html';

$config['entity']['cso_class_province']['path']     = '/Province';
$config['entity']['cso_class_province']['query']    = 'cso_class';
$config['entity']['cso_class_province']['template'] = 'page.class.html';

$config['entity']['cso_class_state']['path']     = '/State';
$config['entity']['cso_class_state']['query']    = 'cso_class';
$config['entity']['cso_class_state']['template'] = 'page.class.html';

$config['entity']['cso_class_traditional-county']['path']     = '/TraditionalCounty';
$config['entity']['cso_class_traditional-county']['query']    = 'cso_class';
$config['entity']['cso_class_traditional-county']['template'] = 'page.class.html';

?>
