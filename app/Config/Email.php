<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Kelas konfigurasi Email untuk CodeIgniter.
 * Digunakan untuk mengatur parameter pengiriman email.
 * 
 * @package Config
 */
class Email extends BaseConfig
{
    /**
     * Alamat email pengirim.
     *
     * @var string
     */
    public string $fromEmail = '';

    /**
     * Nama pengirim yang akan tampil di email.
     *
     * @var string
     */
    public string $fromName = '';

    /**
     * Daftar penerima email (opsional).
     *
     * @var string
     */
    public string $recipients = '';

    /**
     * User agent untuk identifikasi email.
     *
     * @var string
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * Protokol pengiriman email: mail, sendmail, atau smtp.
     *
     * @var string
     */
    public string $protocol = 'mail';

    /**
     * Path untuk sendmail (digunakan jika protokol adalah sendmail).
     *
     * @var string
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * Host server SMTP (digunakan jika protokol adalah smtp).
     *
     * @var string
     */
    public string $SMTPHost = '';

    /**
     * Username SMTP (digunakan jika protokol adalah smtp).
     *
     * @var string
     */
    public string $SMTPUser = '';

    /**
     * Password SMTP (digunakan jika protokol adalah smtp).
     *
     * @var string
     */
    public string $SMTPPass = '';

    /**
     * Port SMTP (25, 465, 587, dll).
     *
     * @var int
     */
    public int $SMTPPort = 25;

    /**
     * Batas waktu koneksi SMTP (dalam detik).
     *
     * @var int
     */
    public int $SMTPTimeout = 5;

    /**
     * Menentukan apakah koneksi SMTP tetap dipertahankan.
     *
     * @var bool
     */
    public bool $SMTPKeepAlive = false;

    /**
     * Jenis enkripsi SMTP: '', 'tls', atau 'ssl'.
     *
     * @var string
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Apakah baris teks di dalam email akan dipecah.
     *
     * @var bool
     */
    public bool $wordWrap = true;

    /**
     * Panjang maksimal baris teks sebelum terpecah.
     *
     * @var int
     */
    public int $wrapChars = 76;

    /**
     * Jenis isi email: 'text' atau 'html'.
     *
     * @var string
     */
    public string $mailType = 'text';

    /**
     * Karakter set yang digunakan (UTF-8, ISO-8859-1, dll).
     *
     * @var string
     */
    public string $charset = 'UTF-8';

    /**
     * Validasi otomatis alamat email.
     *
     * @var bool
     */
    public bool $validate = false;

    /**
     * Prioritas email (1 = tertinggi, 5 = terendah).
     *
     * @var int
     */
    public int $priority = 3;

    /**
     * Karakter newline untuk mematuhi RFC 822.
     *
     * @var string
     */
    public string $CRLF = "\r\n";

    /**
     * Karakter newline untuk mematuhi RFC 822.
     *
     * @var string
     */
    public string $newline = "\r\n";

    /**
     * Mengaktifkan mode BCC batch.
     *
     * @var bool
     */
    public bool $BCCBatchMode = false;

    /**
     * Jumlah email per batch BCC.
     *
     * @var int
     */
    public int $BCCBatchSize = 200;

    /**
     * Mengaktifkan notifikasi dari server (Delivery Status Notification).
     *
     * @var bool
     */
    public bool $DSN = false;
}
