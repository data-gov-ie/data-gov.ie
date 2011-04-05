<?php
/**
 * Methods that handle the data in the query result. Usually called from templates.
 */
class SITE_Template extends LDP_Template
{
    var $sC, $dspl, $currentDSDPrefixValue;

    function __construct($template_filename, $desc, $urispace, $request, $sC)
    {
        //XXX: Beginning of DO NOT MODIFY
        parent::__construct($template_filename, $desc, $urispace, $request, $sC);
        //XXX: End of DO NOT MODIFY
    }


    function dataStructureDefinition()
    {
        $cR = $this->sC->currentRequest[4];

        $search = '#^(/)(dsd)(/)?(.+)?$#i';

        if (preg_match($search, $cR, $matches)) {
            if (isset($matches[4])) {
                return $this->personsBy('dsd:'.$matches[4]);
            }
            else {
                $triples = $this->getTriplesOfType($this->sC->getURI('qb:DataStructureDefinition'));
                $subjects = $this->getSubjects($triples);

                $properties = null;
                $objects = null;
                $triples = $this->getTriples($subjects, $properties, $objects);

                return $this->table_widget->render($triples);
            }
        }

        return;
    }


    function getCurrentDSD()
    {
        $cR = $this->sC->currentRequest[4];

        $search = '#^(/)(dsd)(/)?(.+)?$#i';

        if (preg_match($search, $cR, $matches)) {
            if (isset($matches[4])) {
                $this->currentDSDPrefixValue = $matches[4];

                return $this->sC->getURI('dsd:'.$matches[4]);
            }
        }

        return;
    }


    function personsBy($qname)
    {
        $rT = $rD = $rM = '';

        $triples = $this->getTriples($this->sC->getURI($qname), null, null);
        $rT = $this->table_widget->render($triples);
        $this->excludes = array();

        $tD = $this->getTriplesDimensions($triples);
        $this->table_widget->ignore_properties($this->table_widget->property_order);
        $rD = $this->table_widget->render($tD);
        $this->excludes = array();

        $tM = $this->getTriplesMeasures($triples);
        $this->table_widget->ignore_properties($this->table_widget->property_order);
        $rM = $this->table_widget->render($tM);

        $this->renderClear();

        return $rT.$rD.$rM;
    }


    function getTriplesDimensions($triples)
    {
        return $this->getTriples(null, $this->sC->getURI('qb:dimension'), null, $triples);
    }

    function getTriplesMeasures($triples)
    {
        return $this->getTriples(null, $this->sC->getURI('qb:measure'), null, $triples);
    }


    function createDSPL()
    {
        $this->createDSPLData();

        $qnames = array(
            'geo'      => 'http://www.google.com/publicdata/dataset/google/geo',
            'time'     => 'http://www.google.com/publicdata/dataset/google/time',
            'quantity' => 'http://www.google.com/publicdata/dataset/google/quantity',
            'entity'   => 'http://www.google.com/publicdata/dataset/google/entity'
        );

        $this->xw = new XMLWriter();
        $this->xw->openMemory();
        $this->xw->setIndent(true);
        $this->xw->setIndentString('    ');
        $this->xw->startDocument('1.0', 'UTF-8');
        $this->xw->startElement('dspl');

        $this->xw->writeAttribute('xmlns', 'http://schemas.google.com/dspl/2010');
        foreach($qnames as $prefix => $uri) {
            $this->xw->writeAttribute('xmlns:'.$prefix, $uri);
        }

        foreach($qnames as $prefix => $uri) {
            $this->xw->startElement('import');
            $this->xw->writeAttribute('namespace', $uri);
            $this->xw->endElement();
        }

        $this->createDSPLInfo();
        $this->createDSPLProvider();
        $this->createDSPLTopics();

        $this->createDSPLConcepts();
        $this->createDSPLSlices();
        $this->createDSPLTables();

        $this->xw->endElement();
        $this->xw->endDocument();
        return $this->xw->outputMemory();
    }


    function createDSPLData()
    {
        $dimensionPropertyURI = $this->sC->getURI('qb:dimension');
        $measurePropertyURI = $this->sC->getURI('qb:measure');
        $attributePropertyURI = $this->sC->getURI('qb:attribute');

        $DSDURI = $this->getCurrentDSD();
        $DSDPV = $this->currentDSDPrefixValue;

        $triples = $this->getTriples($DSDURI, array($this->sC->getURI('qb:dimension'), $this->sC->getURI('qb:measure'), $this->sC->getURI('qb:attribute')));

        foreach($triples as $s => $po) {
            foreach($po as $p => $o) {
                foreach($o as $o_key) {
                    $concept = $this->getValue($o_key['value'], 'qb:concept');

                    $label = $this->getValue($concept, 'rdfs:label');

                    //XXX: This is simple.
                    $id = strtolower(str_replace(' ', '-', $label));

                    $tR = $this->getTriples($o_key, $this->sC->getURI('rdfs:range'));

                    $range = $this->getObjects($tR);
                    $range = isset($range[0]) ? $range[0] : '';

                    //XXX: Need to do more decisions here
                    switch($range) {
                        case $this->sC->getURI('xsd:int'):
                            $type = 'integer';
                            break;
                        default:
                            if ($o_key['value'] == $this->sC->getURI('sdmx-dimension:refPeriod')) {
                                $type = 'date';
                            }
                            else {
                                $type = 'string';
                            }
                            break;
                    }

                    // DSPL Concepts
                    $this->dspl['concepts'][$id] = array(
                        'info' => array(
                            'name' => $label,
                            //XXX: Perhaps this can be different.
                            'description' => $label,
                            'url' => $concept
                        ),
                        'type' => $type,
                        'topic' => '',
                        'property' => array(
                            'id' => '',
                            'info' => array(
                                'name' => '',
                                'description', '',
                                'url' => ''
                            ),
                            'concept' => ''
                        ),
                        'table' => $id.'_table'
                    );

                    // DSPL Slices
                    switch($p) {
                        case $dimensionPropertyURI:
                            $this->dspl['slices'][$DSDPV.'_slice']['dimension'][] = $id;
                            break;

                        case $measurePropertyURI:
                            $this->dspl['slices'][$DSDPV.'_slice']['measure'][] = $id;
                            break;

                        case $attributePropertyURI:
                            $this->dspl['slices'][$DSDPV.'_slice']['attribute'][] = $id;
                            break;

                        default:
                            break;
                    }

                    // DSPL Tables
                    $this->dspl['tables'][$id.'_table']['column'] = array(
                        'id' => $id,
                        'type' => $type
                    );
                }

                $this->dspl['tables'][$id.'_table']['data'] = $id.'.csv';
            }
        }

        // DSPL Slices
        $this->dspl['slices'][$DSDPV.'_slice']['table'][] = $DSDPV.'_slice_table';
    }


    function createDSPLInfo()
    {

    }

    function createDSPLProvider()
    {

    }

    function createDSPLTopics()
    {

    }

    function createDSPLConcepts()
    {
        $this->xw->startElement('concepts');
        $this->createConcepts();
        $this->xw->endElement();
    }


    function createConcepts()
    {
        $dspl = $this->dspl;

        foreach ($dspl['concepts'] as $id => $concept) {
            $this->xw->startElement('concept');

            $this->xw->writeAttribute('id', $id);
//        $this->xw->writeAttribute('extends', 'quantity:amount');
            $this->createInfo($concept);
            $this->createConceptType($concept);
            $this->createConceptProperty($concept);
            $this->createConceptTopic($concept);
            $this->createConceptTable($concept);

            $this->xw->endElement();
        }
    }


    /**
     * FIXME: xml:lang value is hard-coded to 'en'
     */
    function createInfo($concept) {
        $this->xw->startElement('info');

        $this->xw->startElement('name');
        $this->xw->startElement('value');
        $this->xw->writeAttribute('xml:lang', 'en');
        $this->xw->text($concept['info']['name']);
        $this->xw->endElement();
        $this->xw->endElement();

        $this->xw->startElement('description');
        $this->xw->startElement('value');
        $this->xw->writeAttribute('xml:lang', 'en');
        $this->xw->text($concept['info']['description']);
        $this->xw->endElement();
        $this->xw->endElement();

        $this->xw->startElement('url');
        $this->xw->startElement('value');
        $this->xw->text($concept['info']['url']);
        $this->xw->endElement();
        $this->xw->endElement();

        $this->xw->endElement();
    }


    function createConceptType($concept)
    {
        $this->xw->startElement('type');
        $this->xw->writeAttribute('ref', $concept['type']);
        $this->xw->endElement();
    }


    function createConceptTopic($concept)
    {
        if (!empty($concept['topic'])) {
            $this->xw->startElement('topic');
            $this->xw->writeAttribute('ref', $concept['topic']);
            $this->xw->endElement();
        }
    }


    function createConceptTable($concept)
    {
        if (!empty($concept['table'])) {
            $this->xw->startElement('table');
            $this->xw->writeAttribute('ref', $concept['table']);
            $this->xw->endElement();
        }
    }


    function createConceptProperty($concept)
    {
        if (!empty($concept['property']['id'])) {
            $this->xw->startElement('property');
            $this->xw->writeAttribute('id', $concept['property']['id']);
            if (!empty($concept['property']['info']['name'])) {
                $this->createInfo($concept['property']);
            }
            $this->xw->endElement();
        }
    }


    function createDSPLSlices()
    {
        $this->xw->startElement('slices');
        $this->createSlices();
        $this->xw->endElement();
    }


    function createSlices()
    {
        $dspl = $this->dspl;

        foreach ($dspl['slices'] as $id => $slices) {
            $this->xw->startElement('slice');
            $this->xw->writeAttribute('id', $id);
            foreach($slices as $componentProperty => $slice) {
                foreach($slice as $value) {
                    $this->createSlice($componentProperty, $value);
                }
            }
            $this->xw->endElement();
        }
    }


    function createSlice($componentProperty, $concept)
    {
        switch($componentProperty) {
            case 'table':
                $this->xw->startElement('table');
                $this->xw->writeAttribute('ref', $concept);
                $this->xw->endElement();
                break;

            default:
                $this->xw->startElement($componentProperty);
                $this->xw->writeAttribute('concept', $concept);
                $this->xw->endElement();
                break;
        }
    }


    function createDSPLTables()
    {
        $this->xw->startElement('tables');
        $this->createTables();
        $this->xw->endElement();
    }


    function createTables()
    {
        $dspl = $this->dspl;

        foreach ($dspl['tables'] as $id_table => $cd) {
            $this->xw->startElement('table');
            $this->xw->writeAttribute('id', $id_table);

            foreach($cd as $key => $value) {
                switch($key) {
                    case 'column':
                        $this->xw->startElement('column');
                        $this->xw->writeAttribute('id', $value['id']);
                        $this->xw->writeAttribute('type', $value['type']);
                        switch ($value['type']) {
                            case 'date':
                                $this->xw->writeAttribute('format', 'yyyy');
                            default:
                                break;
                        }
                        $this->xw->endElement();
                        break;

                    case 'data':
                        $this->xw->startElement('data');
                        $this->xw->startElement('file');
                        $this->xw->writeAttribute('format', 'csv');
                        $this->xw->writeAttribute('encoding', 'utf-8');
                        $this->xw->text($value);
                        $this->xw->endElement();
                        $this->xw->endElement();
                        break;

                    default:
                        break;
                }
            }

            $this->xw->endElement();
        }
    }



    /*
     * TODO: Change bunch of render*() to renderTabularDimensions($object, $dimensions) or renderDimensions()
     * Perhaps $object is a uri, 
     * $dimensions is like array($ns['property']['maritalStatus'], $ns['property']['age2'], $ns['property']['population'])
     */
    function renderMaritalStatusAgePopulation()
    {
        $sC = $this->sC;
        $c  = $sC->getConfig();

        $ns = array();

        //XXX: Would it be better to use the values from index or the config's ns?
        $ns_property                      = 'http://'.$c['server']['stats.govdata.ie'].'/property/';
        $ns['property']['geoArea']        = $ns_property.'geoArea';
        $ns['property']['maritalStatus']  = $ns_property.'maritalStatus';
        $ns['property']['age2']           = $ns_property.'age2';
        $ns['property']['population']     = $ns_property.'population';

        $ns_codeList = 'http://'.$c['server']['stats.govdata.ie'].'/codelist/';
        $ns['prefixes']['codelist']['marital-status'] = $ns_codeList.'marital-status';
        $ns['prefixes']['codelist']['age2'] = $ns_codeList.'age2';

        $resource_uri = $this->desc->get_primary_resource_uri();

        /**
         * This will get only the triples that have maritalStatus age2 population geoArea as property
         */
        $subjects = $this->desc->get_subjects_where_resource($ns['property']['geoArea'], $resource_uri);
        $properties = array($ns['property']['maritalStatus'], $ns['property']['age2'], $ns['property']['population']);
        $objects    = null;
        $triples = $this->getTriples($subjects, $properties, $objects);

        /**
         * This will get the prefLabels of marital-status age2
         */
        $subjects   = $this->desc->get_subjects_where_resource($sC->getURI('skos:topConceptOf'), $ns['prefixes']['codelist']['marital-status']);
        $properties = array($sC->getURI('skos:prefLabel'));
        $objects    = null;
        $triples_propertyLabels = $this->getTriples($subjects, $properties, $objects);

        $triples = array_merge_recursive($triples, $triples_propertyLabels);

        $subjects   = $this->desc->get_subjects_where_resource($sC->getURI('skos:topConceptOf'), $ns['prefixes']['codelist']['age2']);
        $properties = array($sC->getURI('skos:prefLabel'));
        $objects    = null;
        $triples_propertyLabels = $this->getTriples($subjects, $properties, $objects);

        $triples = array_merge_recursive($triples, $triples_propertyLabels);

        $maritalStatusAgePopulation = array();

        foreach($triples as $s => $po) {
            if (isset($po[$ns['property']['maritalStatus']])
                && isset($triples[$po[$ns['property']['maritalStatus']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'])

                && isset($po[$ns['property']['age2']])
                && isset($triples[$po[$ns['property']['age2']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'])

                && isset($po[$ns['property']['population']][0]['value'])) {

                $maritalStatusLabel = $triples[$po[$ns['property']['maritalStatus']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'];
                $ageLabel = $triples[$po[$ns['property']['age2']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'];
                $population = $po[$ns['property']['population']][0]['value'];

                if (array_key_exists($ageLabel, $maritalStatusAgePopulation)
                    && array_key_exists($maritalStatusLabel, $maritalStatusAgePopulation[$ageLabel])) {
                    $maritalStatusAgePopulation[$ageLabel][$maritalStatusLabel] += $population;
                }
                else {
                    $maritalStatusAgePopulation[$ageLabel][$maritalStatusLabel] = $population;
                }
            }
        }

        $r = '';
        $r .= "\n".'<table>';
        $r .= "\n".'<caption>Marital status and age breakdown</caption>';
        $r .= "\n".'<thead><tr><td>Age</td><td>Marital status</td></tr></thead>';
        $r .= "\n".'<tbody>';
        $r .= "\n".'<tr><th></th>';
        //FIXME: Looping over just for this is dirty. Revisit.
        foreach($maritalStatusAgePopulation as $age => $maritalStatusPopulation) {
            foreach($maritalStatusPopulation as $maritalStatus => $population) {
                $r .= "\n".'<th>'.$maritalStatus.'</th>';
            }
            break;
        }
        $r .= "\n".'</tr>';

        foreach($maritalStatusAgePopulation as $age => $maritalStatusPopulation) {
            $r .= "\n".'<tr>';
            $r .= "\n".'<th>'.$age.'</th>';
            foreach($maritalStatusPopulation as $maritalStatus => $population) {
                $r .= "\n".'<td>'.$population.'</td>';
            }
            $r .= "\n".'</tr>';
        }
        $r .= "\n".'</tbody>';
        $r .= "\n".'</table>';

        return $r;
    }


    function renderBirthplace()
    {
        $sC = $this->sC;
        $c  = $sC->getConfig();

        //XXX: Would it be better to use the values from index?
        $ns_property                      = 'http://'.$c['server']['stats.govdata.ie'].'/property/';
        $ns['property']['geoArea']        = $ns_property.'geoArea';
        $ns['property']['birthplace']     = $ns_property.'birthplace';

        $ns_codeList = 'http://'.$c['server']['stats.govdata.ie'].'/codelist/';
        $ns['prefixes']['codelist']['birthplace'] = $ns_codeList.'birthplace';

        $resource_uri = $this->desc->get_primary_resource_uri();

        $subjects = $this->desc->get_subjects_where_resource($ns['property']['geoArea'], $resource_uri);
        $properties = array($ns['property']['birthplace']);
        $objects    = null;
        $triples = $this->getTriples($subjects, $properties, $objects);

        $subjects   = $this->desc->get_subjects_where_resource($sC->getURI('skos:topConceptOf'), $ns['prefixes']['codelist']['birthplace']);
        $properties = array($sC->getURI('skos:prefLabel'));
        $objects    = null;
        $triples_propertyLabels = $this->getTriples($subjects, $properties, $objects);
        $triples = array_merge_recursive($triples, $triples_propertyLabels);

        $r = '';
        $r .= '<dl>';
        $r .= "\n".'<dt>People\'s birthplace</dt>';
        $r .= "\n".'<dd>';
        $r .= "\n".'<ul>';
        foreach($triples as $s => $po) {
            if (isset($po[$ns['property']['birthplace']])
                && isset($triples[$po[$ns['property']['birthplace']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'])) {
                $birthPlaceLabel = $triples[$po[$ns['property']['birthplace']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'];

                $r .= "\n".'<li><a href="'.$po[$ns['property']['birthplace']][0]['value'].'">'.$birthPlaceLabel.'</a></li>';
            }
        }
        $r .= "\n".'</ul>';
        $r .= "\n".'</dd>';
        $r .= "\n".'</dl>';

        return $r;
    }


    function renderReligionPopulation()
    {
        $sC = $this->sC;
        $c  = $sC->getConfig();

        //XXX: Would it be better to use the values from index?
        $ns_property                      = 'http://'.$c['server']['stats.govdata.ie'].'/property/';
        $ns['property']['geoArea']        = $ns_property.'geoArea';
        $ns['property']['religion']       = $ns_property.'religion';
        $ns['property']['population']     = $ns_property.'population';

        $ns_codeList = 'http://'.$c['server']['stats.govdata.ie'].'/codelist/';
        $ns['prefixes']['codelist']['religion']   = $ns_codeList.'religion';
        $ns['prefixes']['codelist']['population'] = $ns_codeList.'population';

        $resource_uri = $this->desc->get_primary_resource_uri();

        $subjects = $this->desc->get_subjects_where_resource($ns['property']['geoArea'], $resource_uri);
        $properties = array($ns['property']['religion'], $ns['property']['population']);
        $objects    = null;
        $triples = $this->getTriples($subjects, $properties, $objects);

        $subjects   = $this->desc->get_subjects_where_resource($sC->getURI('skos:topConceptOf'), $ns['prefixes']['codelist']['religion']);
        $properties = array($sC->getURI('skos:prefLabel'));
        $objects    = null;
        $triples_propertyLabels = $this->getTriples($subjects, $properties, $objects);
        $triples = array_merge_recursive($triples, $triples_propertyLabels);

        $r = '';
        $r .= "\n".'<table>';
        $r .= "\n".'<caption>What are people\'s religion?</caption>';
        $r .= "\n".'<tbody>';
        $r .= "\n".'<tr><th>Religion</th><th># of people</th></tr>';

        foreach($triples as $s => $po) {
            if (isset($po[$ns['property']['religion']])
                && isset($triples[$po[$ns['property']['religion']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'])
                && isset($po[$ns['property']['population']][0]['value'])) {

                $religionLabel = $triples[$po[$ns['property']['religion']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'];
                $religion      = $po[$ns['property']['religion']][0]['value'];
                $population    = $po[$ns['property']['population']][0]['value'];

                $r .= "\n".'<tr><td><a href="'.$religion.'">'.$religionLabel.'</a></td><td>'.$population.'</td></tr>';
            }
        }

        $r .= "\n".'</tbody>';
        $r .= "\n".'</table>';

        return $r;
    }


    function renderUsualResidencePopulation()
    {
        $sC = $this->sC;
        $c  = $sC->getConfig();

        //XXX: Would it be better to use the values from index?
        $ns_property                      = 'http://'.$c['server']['stats.govdata.ie'].'/property/';
        $ns['property']['geoArea']        = $ns_property.'geoArea';
        $ns['property']['usualResidence'] = $ns_property.'usualResidence';
        $ns['property']['population']     = $ns_property.'population';

        $ns_codeList = 'http://'.$c['server']['stats.govdata.ie'].'/codelist/';
        $ns['prefixes']['codelist']['usual-residence']   = $ns_codeList.'usual-residence';
        $ns['prefixes']['codelist']['population'] = $ns_codeList.'population';

        $resource_uri = $this->desc->get_primary_resource_uri();

        $subjects = $this->desc->get_subjects_where_resource($ns['property']['geoArea'], $resource_uri);
        $properties = array($ns['property']['usualResidence'], $ns['property']['population']);
        $objects    = null;
        $triples = $this->getTriples($subjects, $properties, $objects);

        $subjects   = $this->desc->get_subjects_where_resource($sC->getURI('skos:topConceptOf'), $ns['prefixes']['codelist']['usual-residence']);
        $properties = array($sC->getURI('skos:prefLabel'));
        $objects    = null;
        $triples_propertyLabels = $this->getTriples($subjects, $properties, $objects);
        $triples = array_merge_recursive($triples, $triples_propertyLabels);

        $r = '';
        $r .= "\n".'<table>';
        $r .= "\n".'<caption>Where do people usually reside?</caption>';
        $r .= "\n".'<tbody>';
        $r .= "\n".'<tr><th>Location</th><th># of people</th></tr>';

        foreach($triples as $s => $po) {
            if (isset($po[$ns['property']['usualResidence']])
                && isset($triples[$po[$ns['property']['usualResidence']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'])
                && isset($po[$ns['property']['population']][0]['value'])) {

                $usualResidenceLabel = $triples[$po[$ns['property']['usualResidence']][0]['value']][$sC->getURI('skos:prefLabel')][0]['value'];
                $usualResidence      = $po[$ns['property']['usualResidence']][0]['value'];
                $population    = $po[$ns['property']['population']][0]['value'];

                $r .= "\n".'<tr><td><a href="'.$usualResidence.'">'.$usualResidenceLabel.'</a></td><td>'.$population.'</td></tr>';
            }
        }

        $r .= "\n".'</tbody>';
        $r .= "\n".'</table>';

        return $r;
    }
}
?>
