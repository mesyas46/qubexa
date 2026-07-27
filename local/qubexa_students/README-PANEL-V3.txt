Qubexa Student Panel v3 — Registry Architecture

Bu paket uzun vadeli sekme sorunlarını önlemek için hazırlanmıştır.

TEK KAYNAK
Panel sekmeleri artık şu dosyada yönetilir:
classes/output/student_panel_registry.php

Sekme eklemek, kaldırmak veya sıralamak için yalnızca bu dosya
değiştirilir. Mustache sekme menüsünü otomatik üretir.

KORUMA
- Buton ve içerik eşleşmeleri otomatik doğrulanır.
- İçeriği olmayan buton gizlenir.
- Butonu olmayan içerik gizlenir.
- Sekme sayısı otomatik hesaplanır.
- Tüm sekmeler yatay kaydırmayla erişilebilir kalır.
- Klavye sağ/sol ok, Home ve End desteği vardır.
- İlk geçerli sekme güvenli varsayılandır.

KURULUM
1. Mevcut çalışan durumu Git ile kaydedin:
   git add local/qubexa_students
   git commit -m "chore(students): checkpoint before panel v3"

2. ZIP içindeki dosyaları şuraya kopyalayın:
   D:\XAMPP\htdocs\qubexa\local\qubexa_students

3. Mevcut dosyaların üzerine yazmayı onaylayın.

4. Cache temizleyin:
   cd D:\XAMPP\htdocs\qubexa
   D:\XAMPP\php\php.exe admin\cli\purge_caches.php

5. Tarayıcıda Ctrl + F5 yapın.

TEST
- Panel açılıyor.
- Genel, Notlar, Sınavlar, Dersler, Ödemeler, İlerleme ve Timeline
  sekmelerinin tamamı erişilebilir.
- Timeline açılıyor ve filtreler çalışıyor.
- Not, sınav ve ders modülleri çalışıyor.
- Sağ/sol oklarla sekme değişiyor.

Bu paket veritabanını değiştirmez.
Çalışan notes.php, exams.php ve lessons.php dosyalarının üzerine yazmaz.
