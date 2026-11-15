CREATE DATABASE IF NOT EXISTS db_penjualan
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_penjualan;

DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS pelanggan;

CREATE TABLE pelanggan (
  id            VARCHAR(20)  NOT NULL,
  nama          VARCHAR(100) NOT NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  telp          VARCHAR(20),
  alamat        TEXT,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE transaksi (
  id              INT          NOT NULL AUTO_INCREMENT,
  waktu_transaksi DATE         NOT NULL,
  keterangan      TEXT,
  total           INT          NOT NULL,
  pelanggan_id    VARCHAR(20)  NOT NULL,
  PRIMARY KEY (id),
  KEY idx_waktu (waktu_transaksi),
  CONSTRAINT fk_transaksi_pelanggan
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan (id)
) ENGINE=InnoDB;

INSERT INTO pelanggan (id, nama, jenis_kelamin, telp, alamat) VALUES
('P001', 'Andi Pratama',   'L', '081200000001', 'Jl. Melati No. 1'),
('P002', 'Budi Santoso',   'L', '081200000002', 'Jl. Mawar No. 2'),
('P003', 'Citra Lestari',  'P', '081200000003', 'Jl. Anggrek No. 3'),
('P004', 'Dewi Anggraini', 'P', '081200000004', 'Jl. Kenanga No. 4'),
('P005', 'Eko Prasetyo',   'L', '081200000005', 'Jl. Dahlia No. 5'),
('P006', 'Fitri Handayani','P', '081200000006', 'Jl. Flamboyan No. 6'),
('P007', 'Gunawan Saputra','L', '081200000007', 'Jl. Cempaka No. 7'),
('P008', 'Hana Permata',   'P', '081200000008', 'Jl. Teratai No. 8'),
('P009', 'Indra Wijaya',   'L', '081200000009', 'Jl. Seruni No. 9');

INSERT INTO transaksi (waktu_transaksi, keterangan, total, pelanggan_id) VALUES
('2023-09-01', 'Penjualan harian', 15000, 'P001'),
('2023-09-01', 'Penjualan harian', 24000, 'P002'),
('2023-09-02', 'Penjualan harian', 20000, 'P003'),
('2023-09-02', 'Penjualan harian', 32000, 'P004'),
('2023-09-03', 'Penjualan harian', 13000, 'P001'),
('2023-09-04', 'Penjualan harian', 13000, 'P005'),
('2023-09-05', 'Penjualan harian', 13000, 'P006'),
('2023-09-05', 'Penjualan harian', 13000, 'P007'),
('2023-09-06', 'Penjualan harian', 13000, 'P008'),
('2023-09-07', 'Penjualan harian', 13000, 'P009');
