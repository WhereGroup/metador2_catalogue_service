* In den WhereGroup Plugin-Ordner wechseln. Erstellen wenn nicht vorhanden.
 * `cd src/Plugins/WhereGroup`
* Projekt clonen
 * `git clone git@repo.wheregroup.com:metador2/csw.git CatalogueServiceBundle` 
* Plugin nun in der Administration aktivieren

## Beispiele
## GetCapabilities
### GET
- `http://localhost/projects/metador2/web/app_dev.php/csw/service?request=GetCapabilities&service=CSW`

### POST
- `http://localhost/projects/metador2/web/app_dev.php/csw/service`
~~~ xml
<?xml version="1.0" encoding="ISO-8859-1"?>
<GetCapabilities
   xmlns="http://www.opengis.net/cat/csw/2.0.2"
   xmlns:ows="http://www.opengis.net/ows"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.opengis.net/cat/csw/2.0.2 http://schemas.opengis.net/csw/2.0.2/CSW-discovery.xsd"
   service="CSW">
   <ows:AcceptVersions>
      <ows:Version>2.0.2</ows:Version>
      <ows:Version>2.0.0</ows:Version>
      <ows:Version>0.7.2</ows:Version>
   </ows:AcceptVersions>
   <ows:Sections>
       <ows:Section>ServiceIdentification</ows:Section>
       <ows:Section>ServiceProvider</ows:Section>
       <ows:Section>OperationsMetadata</ows:Section>
       <ows:Section>Filter_Capabilities</ows:Section>
   </ows:Sections>
   <ows:AcceptFormats>
      <ows:OutputFormat>application/xml</ows:OutputFormat>
   </ows:AcceptFormats>
</GetCapabilities>
~~~

## DescribeRecord
### GET
- `http://localhost/projects/metador2/web/app_dev.php/csw/service?request=DescribeRecord&service=CSW`

### POST
- `http://localhost/projects/metador2/web/app_dev.php/csw/service`
{% highlight xml %}
<?xml version="1.0" encoding="ISO-8859-1"?>
<DescribeRecord
   service="CSW" 
   version="2.0.2" 
   outputFormat="application/xml"
   schemaLanguage="http://www.w3.org/2001/XMLSchema"
   xmlns="http://www.opengis.net/cat/csw/2.0.2"
   xmlns:csw="http://www.opengis.net/cat/csw/2.0.2"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.opengis.net/cat/csw/2.0.2
                       ../../../csw/2.0.2/CSW-discovery.xsd">
   <TypeName>gmd:MD_Metadata</TypeName>
</DescribeRecord>
{% endhighlight %}

## GetRecordById
### GET
- `http://localhost/projects/metador2/web/app_dev.php/csw/service?service=CSW&request=GetRecordById&version=2.0.2&id=421b22cb-7fa0-4559-85a8-d11beb95f443,421b22cb-7fa0-4559-85a8-d11beb95f443,421b22cb-7fa0-4559-85a8-d11beb95f443,421b22cb-7fa0-4559-85a8-d11beb95f443`

### POST
- `http://localhost/projects/metador2/web/app_dev.php/csw/service`
```
<?xml version="1.0" encoding="ISO-8859-1"?>
<GetRecordById
   service="CSW"
   version="2.0.2"
   outputFormat="application/xml"
   outputSchema="http://www.isotc211.org/2005/gmd"
   xmlns="http://www.opengis.net/cat/csw/2.0.2"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.opengis.net/cat/csw/2.0.2
                       ../../../csw/2.0.2/CSW-discovery.xsd">
   <Id>421b22cb-7fa0-4559-85a8-d11beb95f443</Id>
   <Id>421b22cb-7fa0-4559-85a8-d11beb95f443</Id>
   <Id>421b22cb-7fa0-4559-85a8-d11beb95f443</Id>
   <Id>421b22cb-7fa0-4559-85a8-d11beb95f443</Id>
   <ElementSetName>summary</ElementSetName>
</GetRecordById>
```