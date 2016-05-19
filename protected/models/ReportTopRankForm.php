<?php

/**
 * ReportTopRankForm class.
 * ReportTopRankForm is the data structure for keeping
 * report Top rank form data. It is used by the 'toprank' action of 'ReportController'.
 *
 * The followings are the available model relations:
 */
class ReportTopRankForm extends CFormModel
{

    const SORT_BY_QTY_ASC = 1;
    const SORT_BY_QTY_DSC = 2;
    const SORT_BY_OMZET_ASC = 3;
    const SORT_BY_OMZET_DSC = 4;
    const SORT_BY_MARGIN_ASC = 5;
    const SORT_BY_MARGIN_DSC = 6;
    /* ============= */
    const KERTAS_LETTER = 10;
    const KERTAS_A4 = 20;
    const KERTAS_FOLIO = 30;
    /* ===================== */
    const KERTAS_LETTER_NAMA = 'Letter';
    const KERTAS_A4_NAMA = 'A4';
    const KERTAS_FOLIO_NAMA = 'Folio';

    public $dari;
    public $sampai;
    public $kategoriId;
    public $rakId;
    public $limit = 200;
    public $sortBy;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('dari, sampai, sortBy', 'required', 'message' => '{attribute} tidak boleh kosong'),
            array('kategoriId, rakId, limit', 'safe')
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'kategoriId' => 'Kategori',
            'rakId' => 'Rak',
            'limit' => 'Jumlah Item',
            'sortBy' => 'Urut berdasarkan',
            'dari' => 'Dari',
            'sampai' => 'Sampai'
        );
    }

    public function getNamaKategori()
    {
        $model = KategoriBarang::model()->findByPk($this->kategoriId);
        return $model->nama;
    }

    public function getNamaRak()
    {
        $model = RakBarang::model()->findByPk($this->rakId);
        return $model->nama;
    }

    public function reportTopRank()
    {
        $dari = date_format(date_create_from_format('d-m-Y', $this->dari), 'Y-m-d');
        $sampai = date_format(date_create_from_format('d-m-Y', $this->sampai), 'Y-m-d');

        $command = Yii::app()->db->createCommand();
        $command->select('t_penjualan.barang_id, barang.barcode, barang.nama, t_penjualan.totalqty, t_penjualan.total, t_modal.totalmodal, (t_penjualan.total - t_modal.totalModal) margin');
        $command->from("(SELECT
                                barang_id,
                                SUM(pd.harga_jual * pd.qty) total,
                                SUM(qty) totalqty
                        FROM
                            penjualan_detail pd
                        JOIN penjualan pj ON pd.penjualan_id = pj.id AND pj.status!=:statusDraft
                            AND DATE_FORMAT(pj.tanggal, '%Y-%m-%d') BETWEEN :dari AND :sampai
                        GROUP BY barang_id) t_penjualan");
        $command->join("(SELECT
                            barang_id, SUM(hpp.qty * hpp.harga_beli) totalmodal
                        FROM
                            harga_pokok_penjualan hpp
                        JOIN penjualan_detail pd ON hpp.penjualan_detail_id = pd.id
                        JOIN penjualan pj ON pd.penjualan_id = pj.id AND pj.status!=:statusDraft
                            AND DATE_FORMAT(pj.tanggal, '%Y-%m-%d') BETWEEN :dari AND :sampai
                        GROUP BY barang_id) t_modal", "t_penjualan.barang_id = t_modal.barang_id");
        $command->join('barang','t_penjualan.barang_id=barang.id');
        //$command->order("t_penjualan.nomor");
        //$command->where("t_penjualan.profil_id is not null");

        switch ($this->sortBy) {
            case self::SORT_BY_QTY_DSC:
                $command->order('totalqty desc');
                break;
            case self::SORT_BY_OMZET_DSC:
                $command->order('total desc');
                break;
            case self::SORT_BY_MARGIN_DSC:
                $command->order('(t_penjualan.total - t_modal.totalModal) desc');
                break;
        }

        if ($this->limit != ''){
            $command->limit($this->limit);
        }

        $command->bindValue(":statusDraft", Penjualan::STATUS_DRAFT);
        $command->bindValue(":dari", $dari);
        $command->bindValue(":sampai", $sampai);

        return $command->queryAll();
    }

    public function filterKategori()
    {
        return ['NULL' => '[SEMUA]'] + CHtml::listData(KategoriBarang::model()->findAll(array('order' => 'nama')), 'id', 'nama');
    }

    public function filterRak()
    {
        return ['NULL' => '[SEMUA]'] + CHtml::listData(RakBarang::model()->findAll(array('order' => 'nama')), 'id', 'nama');
    }

    public function listSortBy()
    {
        return [
            self::SORT_BY_QTY_DSC => 'Jumlah Barang [z-a]',
            self::SORT_BY_OMZET_DSC => 'Nominal Penjualan [z-a]',
            self::SORT_BY_MARGIN_DSC => 'Margin [z-a]',
        ];
    }

    public function listKertas()
    {
        return [
            self::KERTAS_A4 => self::KERTAS_A4_NAMA,
            self::KERTAS_FOLIO => self::KERTAS_FOLIO_NAMA,
            self::KERTAS_LETTER => self::KERTAS_LETTER_NAMA
        ];
    }

}
