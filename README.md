* In den WhereGroup Plugin-Ordner wechseln. Erstellen wenn nicht vorhanden.
 * `cd src/Plugins/WhereGroup`
* Projekt clonen
 * `git clone git@repo.wheregroup.com:metador2/csw.git CatalogueServiceBundle` 
* Plugin nun in der Administration aktivieren

## Beispiele
## GetCapabilities
### GET
- (http://localhost/projects/metador2/web/app_dev.php/csw/service?request=GetCapabilities&service=CSW`)


### POST
`<?xml version="1.0" encoding="ISO-8859-1"?>
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
</GetCapabilities>`