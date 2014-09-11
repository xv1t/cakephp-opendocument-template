# OpenDocument Template
## Component for your CakePHP project manual with more screenshots
Warning: this software as is. No Warrenty. in the testing phase

> Documents routine with spreadsheets and text files are hard!
> Make your reports are easely!

> For generating report files .odt or .ods - **do not** require any Office program on the server side

**Firstly** you do create a report templates in the LibreOffice Calc or LibreOffice Writer with special marks and regions in then you file. 

**Upload** this files to the server with CakePHP. 

**Next** In the your project create a standard model query in the CakePHP style and your result data load to the OpenDocumentTemplate Component.

As a result we optain a complete **files for the office packages** in a short time!
> Now about everything in order


### Installation
Is very easy. Put the file in the Controller/Component folder of your project and connect component in the AppController:

```php
public $components = array(
        'OpenDocumentTemplate'
        );
```

## Spreadsheet / *.ods / LibreOffice Calc
Report source file example in the file **continents.ods**.

Get your database data. Test data array see in the file
[Examples/Continents-array.php](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Examples/Continents-array.php)
Such a request can be obtained by models with constraints [$belongsTo](http://book.cakephp.org/2.0/en/models/associations-linking-models-together.html#belongsto) and [$hasMany](http://book.cakephp.org/2.0/en/models/associations-linking-models-together.html#hasmany)  to the parent or details models class data. For more information please read an a official exellent CakePHP book.

> Example this file in the LibreOffice Calc

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_01_Report_file_view.png)
***


All fields marked as: [ModelName.field]. You can in the one cell write a any fields, for example:

> [Continent.name] has area an: [Continent.area]

***


> Manage Names ranges!

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_02_Manage_names.png)
***

> Select the rows, which you want a looped in cicle your data. 

> Remember! In the "Manage Names" set checkbox is "on" the "Repeat row" of the range, for looping data

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_06_continents.png)

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_11.png)
***

> Inside the master region, select the detail range rows

> Remember! In the "Manage Names" set checkbox is "on" the "Repeat row" of the range, for looping data

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_07_countries.png)

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_12.png)
***

> And third level of details data

> Remember! In the "Manage Names" set checkbox is "on" the "Repeat row" of the range, for looping data

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_08_cities.png)

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_14.png)
***

> For test added region with Continents, and named is **Continents__2**

> This Region dos no have a details. This list as a good old simply flat table :)

> Remember! In the "Manage Names" set checkbox is "on" the "Repeat row" of the range, for looping data

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_09_continents_2.png)

***
> If you want added for example Continents in yet another place, name it ModelName__8, or Modelname__7. After the name add double __ and any number

> All regions you must in the options **Scope** set to **Document (Global)**

> You can add a any count of regions, but it's names is uniquie!!! in the LibreOffice Calc

> Remember: Name of named region is key to your data for loop. See to the examples of the test array

### The Result of example
And here is the result that is obtained
![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_17.png)

### Images
> Remember! All images need to be Anchor->To cell

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_15.png)

> and in the dialog "Position and size" set to the checkbox "Size"

![](https://github.com/xv1t/cakephp-opendocument-template/blob/master/Docs/img/ods_16.png)

### Numbers, numeric, float and integers
If in the cell only numeric value, then OpenDocument Component set the type of cell a numeric, and your Calc analyze be was a satisfied. 

### The code for ods
In the Controller, example code
```php
    public function ods_test(){        
        $data = $this->OpenDocumentTemplate->test1_data();
        
        $this->OpenDocumentTemplate->ods(
               '/var/www/ldb/files/backup/continents.ods',
               '/var/www/tmp/continents_render.ods', 
               $data
            );
    }
```
After the work you has a file **/var/www/tmp/continents_render.ods**.
Enjoy!

## Text document / *.odt / LibreOffice Writer
Test case is no!

For .odt file everything is much easier. Make your report source Text document with marks, such as:
> [Document.number] of [Document.dateof]. 
> Client [Client.name] with contractor [Contractor.name]

### The code for odt
In the Controller, for example
```php
    public function odt_test(){        
        $data = $this->OpenDocumentTemplate->test1_data();
        
        $this->OpenDocumentTemplate->odt(
               '/var/www/ldb/files/backup/contract_rep.odt',
                '/var/www/tmp/contract_with_client.odt', 
                $data
                );
    }
```

***

Waiting for feedback and comments.

Thanks
