<?php
/**
 * @param string $filename - address of csv file id;name;sort;brand;picture
 * @author svtoroi
 * @copyright 2018 AeroIdea
 * @return array of arrays of strings
 */


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
$el = new \CIBlockElement();
//echo '<pre>';
//var_dump($el);


function getFileData($filename)
{
    $handle = @fopen($filename, "r");
    $a = [];
    if ($handle) {
        $i = 0;

        while (($buffer = fgets($handle, 4096)) !== false) {
            $x = explode(';', $buffer);
            if (count($x) == 5) {
                $id = $x[0];
                $name = $x[1];
                $sort = $x[2];
                $brends = $x[3];
                $png = $x[4];
                $a[$i]['id'] = $id;
                $a[$i]['name'] = $name;
                $a[$i]['sort'] = $sort;
                $a[$i]['brends'] = $brends;
                $a[$i]['png'] = $png;

            }
            $i++;
        }
        if (!feof($handle)) {
            echo "Ошибка: fgets() неожиданно потерпел неудачу";
        }
        fclose($handle);
    }
    return $a;
}

/**
 * @param array $a - array elments with properties: id;name;sort;brand;picture
 * @param  $id - IBLOCKE_ID in in which we work

 * @author svtoroi
 * @copyright 2018 AeroIdea
 * @return updated iblock
 */
function addIBlockElem ($a, $id)
{
    $arrayElemnts = array();
    if (CModule::IncludeModule("iblock"))
    {
        for ($i = 0; $i < Count($a);$i++)
        {
            $arrayElemnts [$i]= new CIBlockElement();
            $PROP = array();
            $PROP[5] = $a[$i]['brends'];

            $arLoadProductArray = array(

                "IBLOCK_SECTION_ID" => catalog,
                "IBLOCK_ID"      => 2,
                "EXTERNAL_ID" => $a[$i]['id'],
                "NAME"           => $a[$i]['name'],
                "CODE" => $a[$i]['id'],
                "SORT" => $a[$i]['sort'],
                "PROPERTY_VALUES" => $PROP,
                "ACTIVE"         => "Y",            // активен
                "BACKGROUND_IMAGE" => CFile::MakeFileArray($a[$i]['png'])
            );

            if($PRODUCT_ID = $arrayElemnts[$i]->Add($arLoadProductArray)) {
                echo "New ID: ".$PRODUCT_ID;}
            else{
                $arFilter = array("IBLOCK_ID" => 3, "PROPERTY_VALUES"[64] => 'imported', "CODE" => $a[$i]['id']);

                $res = CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 1), array('ID', 'CODE', 'NAME', 'PROPERTY_VALUES'));
                $element = array();
                $element[$i] = $res->Fetch();
                $arrayElemnts[$i]->Update($element[$i]['ID'], $arLoadProductArray);
                $res = CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 1), array('ID', 'CODE', 'NAME', 'PROPERTY_VALUES'));
                $element[$i] = $res->Fetch();
            }

        }
    }
}

/**
 * @param $a - array elments with properties: id;name;sort;brand;picture
 * @param $id - IBLOCKE_ID in which we work
 * @author svtoroi
 * @copyright 2018 AeroIdea
 * @delits element IBlock
 */
function deleteIBlockElements($a, $id){
    for ($i = 0; $i < Count($a);$i++) {
        $arFilter = array("IBLOCK_ID" => $id, "IBLOCK_SECTION_ID" => false, "PROPERTY_VALUES" [5] => $a[$i]['brends']);
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array('ID', 'CODE'));
        $actualElems = array();
        while ($ar_fields = $res->GetNext()) {
            for ($i = 0; $i < Count($a); $i++) {
                if ($ar_fields['CODE'] == $a[$i]['id']) {
                    $actualElems[] = $ar_fields['ID'];
                }
            }
        }

        echo 'УДАЛЯЕМ ЭЛЕМЕНТЫ ID КОТОРЫХ НЕТ В МАССИВЕ';
        echo '<pre>', var_dump($actualElems), '</pre>';
        //НЕТ В МАССИВЕ $actualElems - УДАЛИТЬ
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array('ID', 'CODE'));
        while ($ar_fields = $res->GetNext()) {
            //for ($i = 0; $i < Count($actualElems); $i++)
            if (!in_array($ar_fields['ID'], $actualElems)) {
                if (CIBlockElement::Delete($ar_fields['ID']))
                    echo "УДАЛЕН ЭЛЕМЕНТ № ", $ar_fields['ID'];
            }
        }

        echo '<br>';
        echo '<br>';
    }
}

$a = getFileData('../local/import.csv');


//addIBlockElem($a,5);
echo '<pre>';
var_dump(addIBlockElem($a,2));

?>