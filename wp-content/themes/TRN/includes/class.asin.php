<?php
class Asin {


    var $asin;
    var $temp;



    function __construct($asin=null)
    {
      //  $this->get_asin_data($asin);
    }



    function listProducts() {


    $temp = "";
// get local products first
    $arr = array('0' => 'B0036A98ZO');
    return $arr;


        //return  self::constant . "\n";
    }

    function getProduct($asin) {

      $asin = "B0036A98ZO";

      $temp =  '{"attributes":{"Binding":"DVD","EAN":"4049834003036","EANList":{"EANListElement":"4049834003036"},"IsEligibleForTradeIn":"1","ItemDimensions":{"Height":"780","Length":"591","Width":"539"},"Languages":{"Language":[{"Name":"German","Type":"Published"},{"Name":"German","Type":"Subtitled"},{"Name":"Italian","Type":"Dubbed"},{"Name":"English","Type":"Dubbed"},{"Name":"German","Type":"Original Language"}]},"MPN":"DVD923-944","NumberOfDiscs":"20","PackageDimensions":{"Height":"551","Length":"772","Weight":"353","Width":"583"},"PartNumber":"DVD923-944","ProductGroup":"DVD","ProductTypeName":"ABIS_DVD","Title":"Bud Spencer & Terence Hill Monster-Box Reloaded","TradeInValue":{"Amount":"3558","CurrencyCode":"USD","FormattedPrice":"$35.58"}},"images":{"ImageSet":[{"@attributes":{"Category":"variant"},"SwatchImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61GKeboT7tL._SL30_.jpg","Height":"28","Width":"30"},"SmallImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61GKeboT7tL._SL75_.jpg","Height":"71","Width":"75"},"ThumbnailImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61GKeboT7tL._SL75_.jpg","Height":"71","Width":"75"},"TinyImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61GKeboT7tL._SL110_.jpg","Height":"104","Width":"110"},"MediumImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61GKeboT7tL._SL160_.jpg","Height":"152","Width":"160"},"LargeImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61GKeboT7tL.jpg","Height":"474","Width":"500"}},{"@attributes":{"Category":"variant"},"SwatchImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/51hVUlb55BL._SL30_.jpg","Height":"30","Width":"27"},"SmallImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/51hVUlb55BL._SL75_.jpg","Height":"75","Width":"67"},"ThumbnailImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/51hVUlb55BL._SL75_.jpg","Height":"75","Width":"67"},"TinyImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/51hVUlb55BL._SL110_.jpg","Height":"110","Width":"98"},"MediumImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/51hVUlb55BL._SL160_.jpg","Height":"160","Width":"143"},"LargeImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/51hVUlb55BL.jpg","Height":"500","Width":"446"}},{"@attributes":{"Category":"variant"},"SwatchImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/511b47cbxKL._SL30_.jpg","Height":"30","Width":"26"},"SmallImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/511b47cbxKL._SL75_.jpg","Height":"75","Width":"65"},"ThumbnailImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/511b47cbxKL._SL75_.jpg","Height":"75","Width":"65"},"TinyImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/511b47cbxKL._SL110_.jpg","Height":"110","Width":"95"},"MediumImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/511b47cbxKL._SL160_.jpg","Height":"160","Width":"139"},"LargeImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/511b47cbxKL.jpg","Height":"500","Width":"434"}},{"@attributes":{"Category":"primary"},"SwatchImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61B7V1GnVGL._SL30_.jpg","Height":"30","Width":"26"},"SmallImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61B7V1GnVGL._SL75_.jpg","Height":"75","Width":"65"},"ThumbnailImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61B7V1GnVGL._SL75_.jpg","Height":"75","Width":"65"},"TinyImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61B7V1GnVGL._SL110_.jpg","Height":"110","Width":"95"},"MediumImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61B7V1GnVGL._SL160_.jpg","Height":"160","Width":"138"},"LargeImage":{"URL":"http:\/\/ecx.images-amazon.com\/images\/I\/61B7V1GnVGL.jpg","Height":"500","Width":"431"}}]}}';

      $arr = json_decode($temp , true);
      $arr['asin'] = $asin;
      return $arr;

  }


  function syncProducts($apikey)
  {

  }

}


  ?>
