Qubexa Student Timeline

Bu paket yeni veritabanı tablosu oluşturmaz.
Mevcut öğrenci, not, sınav ve ders tablolarını birleştirir.

KURULUM
1. ZIP içindeki dosyaları şu klasöre kopyalayın:
   D:\XAMPP\htdocs\qubexa\local\qubexa_students

2. Mevcut dosyaların üzerine yazmayı onaylayın:
   - lib.php
   - classes/output/students_page.php
   - templates/students_page.mustache

3. Yeni dosyalar:
   - ajax/timeline.php
   - classes/repository/timeline_repository.php
   - classes/service/timeline_service.php
   - student_timeline.js
   - student_timeline.css

4. Cache temizleyin:
   cd D:\XAMPP\htdocs\qubexa
   D:\XAMPP\php\php.exe admin\cli\purge_caches.php

5. Tarayıcıda Ctrl + F5 yapın.

TEST
- Öğrenciyi açın.
- Timeline sekmesine geçin.
- Not, Sınav ve Ders olaylarının tarih sırasıyla geldiğini kontrol edin.
- Tümü, Notlar, Sınavlar ve Dersler filtrelerini deneyin.
- Yenile düğmesini test edin.

Ödemeler tablosu kurulduğunda Timeline servisine ödeme olayları da
ayrı bir güvenli güncellemeyle eklenecektir.
