-- Daftar harga Oktober 2026, wilayah Bogor
-- Jalankan SATU KALI. Cadangkan dulu: mysqldump -u root bank_sampah > backup.sql
-- Cara: mysql -u root bank_sampah < migrations/2026_10_harga_oktober.sql

-- 1. Kolom satuan (Kg / Pcs / Unit). Data lama otomatis berisi 'Kg'.
ALTER TABLE jenis_sampah ADD COLUMN satuan VARCHAR(10) NOT NULL DEFAULT 'Kg';

-- 2. Tandai batas data lama
SET @id_awal = (SELECT COALESCE(MAX(id), 0) FROM jenis_sampah);

-- 3. Data lama DINONAKTIFKAN (bukan dihapus) supaya riwayat transaksi tetap utuh
UPDATE jenis_sampah SET is_active = 0 WHERE id <= @id_awal;

-- 4. Data harga baru
INSERT INTO jenis_sampah (nama, kategori, poin_per_kg, harga_per_kg, satuan, deskripsi, is_active) VALUES
  ('Kardus', 'kertas', 0, 1700, 'Kg', '', 1),
  ('Putihan', 'kertas', 0, 1500, 'Kg', '', 1),
  ('Buku', 'kertas', 0, 1000, 'Kg', '', 1),
  ('Majalah', 'kertas', 0, 700, 'Kg', '', 1),
  ('Duplex', 'kertas', 0, 500, 'Kg', '', 1),
  ('Koran A', 'kertas', 0, 6000, 'Kg', '', 1),
  ('LKS', 'kertas', 0, 700, 'Kg', '', 1),
  ('Kertas Semen', 'kertas', 0, 1500, 'Kg', '', 1),
  ('Tetrapack', 'kertas', 0, 200, 'Kg', '', 1),
  ('Besi', 'logam', 0, 3500, 'Kg', '', 1),
  ('Besi Campur', 'logam', 0, 2500, 'Kg', '', 1),
  ('Babet', 'logam', 0, 10000, 'Kg', '', 1),
  ('AL Panci', 'logam', 0, 20000, 'Kg', '', 1),
  ('AL Rongsok', 'logam', 0, 16000, 'Kg', '', 1),
  ('AL Siku', 'logam', 0, 25000, 'Kg', '', 1),
  ('Kaleng', 'logam', 0, 2000, 'Kg', '', 1),
  ('AKI', 'logam', 0, 10000, 'Kg', '', 1),
  ('Tembaga', 'logam', 0, 100000, 'Kg', '', 1),
  ('Kuningan', 'logam', 0, 50000, 'Kg', '', 1),
  ('Asoy Murni Kresek', 'plastik', 0, 500, 'Kg', '', 1),
  ('Selopan Murni', 'plastik', 0, 500, 'Kg', '', 1),
  ('Plastik Daunan PP/PE', 'plastik', 0, 700, 'Kg', '', 1),
  ('Bonet', 'plastik', 0, 600, 'Kg', '', 1),
  ('Nilek/Selang', 'plastik', 0, 600, 'Kg', '', 1),
  ('CD', 'plastik', 0, 5000, 'Kg', '', 1),
  ('Emberan', 'plastik', 0, 1500, 'Kg', '', 1),
  ('Ember Hitam', 'plastik', 0, 1000, 'Kg', '', 1),
  ('Galon Aqua', 'plastik', 0, 5000, 'Kg', '', 1),
  ('Glas A', 'plastik', 0, 4500, 'Kg', '', 1),
  ('Glas B', 'plastik', 0, 2000, 'Kg', '', 1),
  ('Impek', 'plastik', 0, 500, 'Kg', '', 1),
  ('Kabel/Nilek', 'plastik', 0, 700, 'Kg', '', 1),
  ('Kristal', 'plastik', 0, 5000, 'Kg', '', 1),
  ('Kristal Warna', 'plastik', 0, 1500, 'Kg', '', 1),
  ('Mainan/HDPE', 'plastik', 0, 2000, 'Kg', '', 1),
  ('Monti', 'plastik', 0, 1500, 'Kg', '', 1),
  ('Naso Injek', 'plastik', 0, 4000, 'Kg', '', 1),
  ('Naso Natural', 'plastik', 0, 3500, 'Kg', '', 1),
  ('Naso PK', 'plastik', 0, 3000, 'Kg', '', 1),
  ('Paralon', 'plastik', 0, 700, 'Kg', '', 1),
  ('PET A Bening', 'plastik', 0, 5000, 'Kg', '', 1),
  ('PET A Biru', 'plastik', 0, 5000, 'Kg', '', 1),
  ('PET Warna', 'plastik', 0, 1500, 'Kg', '', 1),
  ('PET Dove/PK', 'plastik', 0, 500, 'Kg', '', 1),
  ('PET B Campur', 'plastik', 0, 2000, 'Kg', '', 1),
  ('Tutup Botol', 'plastik', 0, 3000, 'Kg', '', 1),
  ('Tutup Galon', 'plastik', 0, 6000, 'Kg', '', 1),
  ('Botol Beling', 'kaca', 0, 300, 'Kg', '', 1),
  ('Jelantah', 'lainnya', 0, 6000, 'Kg', '', 1),
  ('Ban Mobil', 'lainnya', 0, 500, 'Kg', '', 1),
  ('Ban Motor', 'lainnya', 0, 1000, 'Pcs', 'Harga per pcs', 1),
  ('Mesin Cuci Rusak', 'elektronik', 0, 40000, 'Unit', 'Kisaran Rp 40.000 - 50.000 per unit', 1),
  ('Kulkas Rusak', 'elektronik', 0, 50000, 'Unit', 'Kisaran Rp 50.000 - 60.000 per unit', 1),
  ('TV Tabung/Monitor', 'elektronik', 0, 15000, 'Unit', 'Harga per unit', 1),
  ('TV LCD', 'elektronik', 0, 10000, 'Unit', 'Harga per unit', 1),
  ('HP, Tablet', 'elektronik', 0, 3000, 'Unit', 'Kisaran Rp 3.000 - 5.000 per unit', 1),
  ('Galon Limineral', 'lainnya', 0, 1100, 'Pcs', 'Harga per pcs', 1),
  ('Galon Cleo', 'lainnya', 0, 1100, 'Pcs', 'Harga per pcs', 1),
  ('Jirigen 5 Liter', 'lainnya', 0, 700, 'Pcs', 'Harga per pcs', 1),
  ('Gabruk', 'lainnya', 0, 1000, 'Kg', '', 1);

-- 5. Poin per satuan. SESUAIKAN RUMUSNYA dengan aturan poin Trashily.
--    Sementara: 1 poin per Rp 100 (minimal 1 poin).
UPDATE jenis_sampah
SET poin_per_kg = GREATEST(1, ROUND(harga_per_kg / 100))
WHERE id > @id_awal;
