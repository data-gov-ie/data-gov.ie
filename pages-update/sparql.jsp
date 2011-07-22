<%@ page import="org.openjena.fuseki.mgt.*"%>
<%@ page import="java.util.*"%>
<%@ page contentType="text/html ; charset=UTF-8"%>
<%@ page isThreadSafe="true"%>

<html>
  <head>
    <title>Fuseki</title>
    <link rel="stylesheet" type="text/css" href="fuseki.css" />
  </head>
  <body>
    <h1>Fuseki Query</h1>
    Dataset: <%= Functions.dataset(request, "No Session") %>
    <hr/>

    <% String ds = Functions.dataset(request) ; %>

    <p><b>SPARQL Query</b></p>
    <div class="moreindent">
      <form action="<%= ds%>/query" method="get">
        <textarea  style="background-color: #F0F0F0;" name="query" cols="100" rows="40">
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX wgs: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX dcat: <http://www.w3.org/ns/dcat#>
PREFIX sdmx: <http://purl.org/linked-data/sdmx#>
PREFIX sdmx-attribute: <http://purl.org/linked-data/sdmx/2009/attribute#>
PREFIX sdmx-code: <http://purl.org/linked-data/sdmx/2009/code#>
PREFIX sdmx-concept: <http://purl.org/linked-data/sdmx/2009/concept#>
PREFIX sdmx-dimension: <http://purl.org/linked-data/sdmx/2009/dimension#>
PREFIX sdmx-measure: <http://purl.org/linked-data/sdmx/2009/measure#>
PREFIX sdmx-metadata: <http://purl.org/linked-data/sdmx/2009/metadata#>
PREFIX sdmx-subject: <http://purl.org/linked-data/sdmx/2009/subject#>
PREFIX qb: <http://purl.org/linked-data/cube#>
PREFIX year: <http://reference.data.gov.uk/id/year/>
PREFIX statsDataGov: <http://stats.govdata.ie/>
PREFIX concept: <http://stats.govdata.ie/concept/>
PREFIX codelist: <http://stats.govdata.ie/codelist/>
PREFIX dsd: <http://stats.govdata.ie/dsd/>
PREFIX property: <http://stats.govdata.ie/property/>
PREFIX geoDataGov: <http://geo.govdata.ie/>
PREFIX sch-ont: <http://education.data.gov.uk/ontology/school#>

</textarea>
        <br/>

        Output: <select name="output">
          <option value="xml">XML</option>
          <option value="json">JSON</option>
          <option value="text">Text</option>
          <option value="csv">CSV</option>
          <option value="tsv">TSV</option>
        </select>
        <br/>
	    XSLT style sheet (blank for none): 
        <input name="stylesheet" size="20" value="/xml-to-html.xsl" />
        <br/>
        <input type="checkbox" name="force-accept" value="text/plain"/>
        Force the accept header to <tt>text/plain</tt> regardless 
	    <br/>
	    <input type="submit" value="Get Results" />
      </form>
    </div>
    <hr/>

    <p><b>SPARQL Update</b></p>
    <div class="moreindent">
      <form action="<%= ds %>/update" method="post">
        <textarea style="background-color: #F0F0F0;" name="update" cols="70" rows="10"></textarea>
	    <br/>
        <input type="submit" value="Perform update" />
      </form>
    </div>
    <hr/>
    <p><b>File upload</b></p>
    <div class="moreindent">
      <form action="<%= ds %>/upload" enctype="multipart/form-data" method="post">
        File: <input type="file" name="UNSET FILE NAME" size="40"><br/>
        Graph: <input name="graph" size="20" value="default"/><br/>
        <input type="submit" value="Upload">
      </form>
    </div>
    <hr/>
      </body>
</html>   

