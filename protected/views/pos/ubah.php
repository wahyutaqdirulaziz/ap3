<?php
/* @var $this PosController */
/* @var $model Penjualan */

$this->breadcrumbs = array(
    'Penjualan' => array('index'),
    $model->id => array('view', 'id' => $model->id),
    'Ubah',
);

$this->boxHeader['small'] = 'Ubah';
$this->boxHeader['normal'] = "Penjualan: {$model->nomor}";
?>

<div class="medium-6 large-7 columns">
   <div id="transaksi">
      <?php
      $this->renderPartial('_detail', array(
          'penjualan' => $model,
          'penjualanDetail' => $penjualanDetail
      ));
      ?>
   </div>
</div>
<div class="medium-4 large-3 columns sidebar kanan">
   <div class="row collapse">
      <div class="small-3 large-2 columns">
         <span class="prefix" id="scan-icon"><i class="fa fa-barcode fa-2x"></i></span>
      </div>
      <div class="small-6 large-8 columns">
         <input id="scan" type="text"  placeholder="Scan [B]arcode / Input nama" accesskey="b" autofocus="autofocus"/>
      </div>
      <div class="small-3 large-2 columns">
         <a href="#" class="button postfix" id="tombol-tambah-barang"><i class="fa fa-level-down fa-2x fa-rotate-90"></i></a>
      </div>
   </div>
   <!--      <div class="row collapse">
            <div class="small-3 large-2 columns">
               <span class="prefix huruf"><b>Q</b>ty</span>
            </div>
            <div class="small-6 large-7 columns">
               <input type="text"  value="1" placeholder="[Q]ty" accesskey="q"/>
            </div>
            <div class="small-3 large-3 columns">
               <a href="#" class="button postfix">Tambah</a>
            </div>
         </div>-->
   <!--   <form>
         <div class="row collapse">
            <div class="small-3 large-2 columns">
               <span class="prefix"><i class="fa fa-search fa-2x"></i></span>
            </div>
            <div class="small-6 large-7 columns">
               <input type="text"  placeholder="[C]ari Barang" accesskey="c"/>
            </div>
            <div class="small-3 large-3 columns">
               <a href="#" class="button postfix">Cari</a>
            </div>
         </div>
      </form>-->
   <div id="total-belanja">
      <?php echo $model->getTotal(); ?>
   </div>
   <div id="kembali">
      0
   </div>
   <div class="row collapse">
      <div class="small-3 large-2 columns">
         <span class="prefix"><i class="fa fa-2x fa-bars"></i></span>
      </div>
      <div class="small-6 large-7 columns">
         <select accesskey="a">
            <option value="1">Cash</option>
            <option value="2">Transfer</option>
            <option value="3">Debit</option>
            <option value="4">Kredit</option>
         </select>
      </div>
      <div class="small-3 large-3 columns">
         <span class="postfix"><kbd>Alt</kbd> <kbd>a</kbd></span>
      </div>
   </div>	
   <!--   <div class="row collapse">
         <div class="small-3 large-2 columns">
            <span class="prefix"><i class="fa fa-credit-card fa-2x"></i></span>
         </div>
         <div class="small-6 large-8 columns">
            <input type="text"  placeholder="Surcharge"/>
         </div>
         <div class="small-3 large-2 columns">
            <span class="postfix huruf">%</span>
         </div>
      </div>-->
   <div class="row collapse">
      <div class="small-3 large-2 columns">
         <span class="prefix huruf">IDR</span>
      </div>
      <div class="small-9 large-10 columns">
         <input type="text" id="uang-dibayar" placeholder="[U]ang Dibayar" accesskey="u"/>
      </div>
   </div>
   <a href="" class="button" id="tombol-simpan">Simpan</a>
   <a href="" class="secondary button" id="tombol-batal">Batal</a>
</div>
<input type="hidden" id="total-belanja-h" value="<?php echo $model->ambilTotal(); ?>"/>
<?php
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/jquery.gritter.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl.'/js/vendor/jquery.gritter.min.js', CClientScript::POS_HEAD);
?>
<script>
   $("#uang-dibayar").change(function () {
      var kembali = $(this).val() - $("#total-belanja-h").val();
      console.log("this:" + $(this).val() + "; total:" + $("#total-belanja-h").val());
      mykembali = NumberFormat.getInstance().format(kembali);
      $("#kembali").text(mykembali);
   });

   $(function () {
      $(document).on('click', "#tombol-tambah-barang", function () {
         dataUrl = '<?php echo $this->createUrl('tambahbarang', array('id' => $model->id)); ?>';
         dataKirim = {barcode: $("#scan").val()};
         console.log(dataUrl);
         $.ajax({
            type: 'POST',
            url: dataUrl,
            data: dataKirim,
            success: function (data) {
               if (data.sukses) {
                  $.fn.yiiGridView.update('penjualan-detail-grid');
                  updateTotal();
               } else {
                  $.gritter.add({
                     title: 'Error ' + data.error.code,
                     text: data.error.msg,
                     time: 3000,
                     //class_name: 'gritter-center'
                  });
               }
               $("#scan").val("");
               $("#scan").focus();
            }
         });
         return false;
      });
   });

   $("#scan").keyup(function (e) {
      if (e.keyCode === 13) {
         $("#tombol-tambah-barang").click();
      }
   });

   $("#scan").autocomplete({
      source: "<?php echo $this->createUrl('caribarang'); ?>",
      minLength: 3,
      search: function (event, ui) {
         $("#scan-icon").html('<img src="<?php echo Yii::app()->theme->baseUrl; ?>/css/3.gif" />');
      },
      response: function (event, ui) {
         $("#scan-icon").html('<i class="fa fa-barcode fa-2x"></i>');
      },
      select: function (event, ui) {
         console.log(ui.item ?
                 "Nama: " + ui.item.label + "; Barcode " + ui.item.value :
                 "Nothing selected, input was " + this.value);
         if (ui.item) {
            $("#scan").val(ui.item.value);
         }
      }
   }).autocomplete("instance")._renderItem = function (ul, item) {
      return $("<li>")
              .append("<a>" + item.label + "<br /><small>" + item.value + " [" + item.stok + "][" + item.harga + "]</small></a>")
              .appendTo(ul);
   };

   function updateTotal() {
      var dataurl = "<?php echo Yii::app()->createUrl('penjualan/total', array('id' => $model->id)); ?>";
      $.ajax({
         url: dataurl,
         type: "GET",
         dataType: "json",
         success: function (data) {
            if (data.sukses) {
               $("#total-belanja-h").val(data.total);
               $("#total-belanja").text(data.totalF);
               console.log(data.totalF);
            }
         }
      });
   }

</script>
<?php
$this->menu = array(
    array('itemOptions' => array('class' => 'divider'), 'label' => false),
    array('itemOptions' => array('class' => 'has-form hide-for-small-only'), 'label' => false,
        'items' => array(
            array('label' => '<i class="fa fa-plus"></i> <span class="ak">T</span>ambah', 'url' => $this->createUrl('tambah'), 'linkOptions' => array(
                    'class' => 'button',
                    'accesskey' => 't'
                )),
            array('label' => '<i class="fa fa-asterisk"></i> <span class="ak">I</span>ndex', 'url' => $this->createUrl('index'), 'linkOptions' => array(
                    'class' => 'success button',
                    'accesskey' => 'i'
                ))
        ),
        'submenuOptions' => array('class' => 'button-group')
    ),
    array('itemOptions' => array('class' => 'has-form show-for-small-only'), 'label' => false,
        'items' => array(
            array('label' => '<i class="fa fa-plus"></i>', 'url' => $this->createUrl('tambah'), 'linkOptions' => array(
                    'class' => 'button',
                )),
            array('label' => '<i class="fa fa-asterisk"></i>', 'url' => $this->createUrl('index'), 'linkOptions' => array(
                    'class' => 'success button',
                ))
        ),
        'submenuOptions' => array('class' => 'button-group')
    )
);