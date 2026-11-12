<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mata_uang_model extends CI_Model
{
    protected $table = 'mata_uang';
    protected $primary_key = 'id_kurs';

    // Pemetaan nama negara Indonesia <-> Inggris untuk sinkronisasi akurat
    protected $country_aliases = [
        'afganistan' => ['afghanistan'],
        'afrika selatan' => ['south africa'],
        'afrika tengah' => ['central african republic'],
        'aljazair' => ['algeria'],
        'amerika serikat' => ['united states', 'usa', 'united states of america'],
        'arab saudi' => ['saudi arabia'],
        'belanda' => ['netherlands'],
        'belgia' => ['belgium'],
        'bosnia dan herzegovina' => ['bosnia and herzegovina'],
        'brasil' => ['brazil'],
        'britania raya' => ['united kingdom', 'uk', 'great britain'],
        'ceko (republik ceko)' => ['czech republic', 'czechia'],
        'china' => ['china', 'tiongkok'],
        'tiongkok' => ['china', 'tiongkok'],
        'filipina' => ['philippines'],
        'finlandia' => ['finland'],
        'hongaria' => ['hungary'],
        'inggris' => ['united kingdom', 'england', 'britania raya'],
        'irlandia' => ['ireland'],
        'islandia' => ['iceland'],
        'italia' => ['italy'],
        'jepang' => ['japan'],
        'jerman' => ['germany'],
        'kamboja' => ['cambodia'],
        'kamerun' => ['cameroon'],
        'kanada' => ['canada'],
        'kolombia' => ['colombia'],
        'korea selatan' => ['south korea', 'korea, south', 'republic of korea'],
        'korea utara' => ['north korea', 'korea, north'],
        'kosta rika' => ['costa rica'],
        'kroasia' => ['croatia'],
        'kuba' => ['cuba'],
        'libya' => ['libya'],
        'lituania' => ['lithuania'],
        'luksemburg' => ['luxembourg'],
        'meksiko' => ['mexico'],
        'mesir' => ['egypt'],
        'norwegia' => ['norway'],
        'pantai gading' => ['ivory coast', "cote d'ivoire"],
        'perancis' => ['france'],
        'polandia' => ['poland'],
        'republik ceko' => ['czech republic', 'czechia'],
        'selandia baru' => ['new zealand'],
        'siprus' => ['cyprus'],
        'slowakia' => ['slovakia'],
        'spanyol' => ['spain'],
        'suriah' => ['syria'],
        'swedia' => ['sweden'],
        'swiss' => ['switzerland'],
        'turki' => ['turkey', 'turkiye'],
        'ukraina' => ['ukraine'],
        'uni emirat arab' => ['united arab emirates', 'uae'],
        'yunani' => ['greece']
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ambil semua data mata uang aktif (tidak didelete)
     */
    public function get_all_mata_uang()
    {
        $this->db->select('id_kurs, kode, negara, mata_uang, kurs, activation, created_on, modified_on');
        $this->db->from($this->table);
        $this->db->where('(deleted IS NULL OR deleted = 0)', NULL, FALSE);
        $this->db->order_by('negara', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Ambil 1 record mata uang berdasarkan id_kurs
     */
    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, [
            $this->primary_key => $id,
            '(deleted IS NULL OR deleted = 0)' => NULL
        ])->row();
    }

    /**
     * Normalisasi string nama negara untuk perbandingan
     */
    private function clean_name($str)
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string)$str)));
    }

    /**
     * Cek apakah nama negara sudah terdaftar di table mata_uang
     */
    public function check_country_exists($negara, $current_id = null)
    {
        $negara_clean = $this->clean_name($negara);

        $this->db->select('id_kurs, negara');
        $this->db->from($this->table);
        $this->db->where('(deleted IS NULL OR deleted = 0)', NULL, FALSE);
        if (!empty($current_id)) {
            $this->db->where($this->primary_key . ' !=', $current_id);
        }
        $existing = $this->db->get()->result();

        // Kumpulkan alias untuk negara yang dicek
        $aliases_to_check = [$negara_clean];
        $lower_raw = strtolower(trim((string)$negara));
        if (isset($this->country_aliases[$lower_raw])) {
            foreach ($this->country_aliases[$lower_raw] as $al) {
                $aliases_to_check[] = $this->clean_name($al);
            }
        }
        foreach ($this->country_aliases as $id_name => $en_names) {
            foreach ($en_names as $en) {
                if (strtolower(trim($en)) === $lower_raw) {
                    $aliases_to_check[] = $this->clean_name($id_name);
                }
            }
        }

        foreach ($existing as $row) {
            $row_clean = $this->clean_name($row->negara);
            if (in_array($row_clean, $aliases_to_check)) {
                return true;
            }
            $row_lower = strtolower(trim($row->negara));
            if (isset($this->country_aliases[$row_lower])) {
                foreach ($this->country_aliases[$row_lower] as $row_al) {
                    if (in_array($this->clean_name($row_al), $aliases_to_check)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Ambil daftar negara dari API / Database lokal
     * Dan tentukan status disabled jika sudah terdaftar di mata_uang
     */
    public function get_countries_with_status($current_id = null)
    {
        // 1. Ambil data mata uang yang sudah ada di database
        $this->db->select('id_kurs, kode, negara, mata_uang');
        $this->db->from($this->table);
        $this->db->where('(deleted IS NULL OR deleted = 0)', NULL, FALSE);
        $current_record = null;
        if (!empty($current_id)) {
            $current_record = $this->db->get_where($this->table, [$this->primary_key => $current_id])->row();
        }
        $existing_rows = $this->db->get()->result();

        // Buat map negara yang sudah terdaftar
        $existing_normalized = [];
        foreach ($existing_rows as $row) {
            if (!empty($current_id) && $row->id_kurs == $current_id) {
                continue;
            }

            $clean = $this->clean_name($row->negara);
            $existing_normalized[$clean] = $row->kode;

            $lower = strtolower(trim($row->negara));
            if (isset($this->country_aliases[$lower])) {
                foreach ($this->country_aliases[$lower] as $al) {
                    $al_clean = $this->clean_name($al);
                    $existing_normalized[$al_clean] = $row->kode;
                }
            }
            foreach ($this->country_aliases as $id_name => $en_names) {
                foreach ($en_names as $en) {
                    if (strtolower(trim($en)) === $lower) {
                        $id_clean = $this->clean_name($id_name);
                        $existing_normalized[$id_clean] = $row->kode;
                    }
                }
            }
        }

        // 2. Coba fetch dari REST API
        $countries_data = [];
        $fetched_from_api = false;

        $api_url = 'https://countriesnow.space/api/v0.1/countries/currency';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && !empty($response)) {
            $json = json_decode($response, true);
            if (!empty($json['data']) && is_array($json['data'])) {
                foreach ($json['data'] as $item) {
                    if (!empty($item['name'])) {
                        $countries_data[] = [
                            'name'          => trim($item['name']),
                            'currency_code' => !empty($item['currency']) ? strtoupper(trim($item['currency'])) : '',
                            'iso2'          => $item['iso2'] ?? '',
                            'iso3'          => $item['iso3'] ?? '',
                        ];
                    }
                }
                $fetched_from_api = true;
            }
        }

        // 3. Fallback jika API gagal / offline: ambil dari table `countries`
        if (!$fetched_from_api || empty($countries_data)) {
            $db_countries = $this->db->select('name, country_code, currency')->order_by('name', 'ASC')->get('countries')->result();
            foreach ($db_countries as $c) {
                $countries_data[] = [
                    'name'          => trim($c->name),
                    'currency_code' => !empty($c->currency) ? strtoupper(trim($c->currency)) : '',
                    'iso2'          => '',
                    'iso3'          => $c->country_code ?? '',
                ];
            }
        }

        // 4. Pastikan semua negara dari table mata_uang ada di dalam daftar
        $known_names_clean = [];
        foreach ($countries_data as $cd) {
            $known_names_clean[$this->clean_name($cd['name'])] = true;
        }

        $all_mata_uang = $this->db->get_where($this->table, ['(deleted IS NULL OR deleted = 0)' => NULL])->result();
        foreach ($all_mata_uang as $mu) {
            $mu_clean = $this->clean_name($mu->negara);
            if (!isset($known_names_clean[$mu_clean])) {
                $countries_data[] = [
                    'name'          => trim($mu->negara),
                    'currency_code' => !empty($mu->kode) ? strtoupper(trim($mu->kode)) : '',
                    'iso2'          => '',
                    'iso3'          => '',
                ];
                $known_names_clean[$mu_clean] = true;
            }
        }

        // 5. Susun array hasil dengan status disabled
        $result = [];
        $seen = [];

        foreach ($countries_data as $item) {
            $country_name = $item['name'];
            $clean = $this->clean_name($country_name);

            if (isset($seen[$clean])) {
                continue;
            }
            $seen[$clean] = true;

            $is_disabled = false;
            $status_text = '';

            if (isset($existing_normalized[$clean])) {
                $is_disabled = true;
                $status_text = 'Sudah ditambahkan (' . $existing_normalized[$clean] . ')';
            }

            $is_current = false;
            if ($current_record && $this->clean_name($current_record->negara) === $clean) {
                $is_disabled = false;
                $is_current = true;
                $status_text = 'Saat ini';
            }

            $result[] = [
                'name'          => $country_name,
                'currency_code' => $item['currency_code'],
                'is_disabled'   => $is_disabled,
                'is_current'    => $is_current,
                'status_text'   => $status_text,
                'display_label' => $country_name . ($is_disabled ? ' — [Sudah Terdaftar (' . $existing_normalized[$clean] . ')]' : '')
            ];
        }

        usort($result, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $result;
    }

    /**
     * Simpan mata uang (Insert / Update)
     */
    public function save_data($data, $id = null, $user_id = null)
    {
        $date = date('Y-m-d');

        $kurs = isset($data['kurs']) ? (float) str_replace(',', '', (string)$data['kurs']) : 0.0;

        $payload = [
            'kode'       => strtoupper(trim($data['kode'])),
            'negara'     => trim($data['negara']),
            'mata_uang'  => trim($data['mata_uang']),
            'kurs'       => $kurs,
            'activation' => !empty($data['activation']) ? $data['activation'] : 'active',
            'modified_on'=> $date,
            'modified_by'=> $user_id,
        ];

        $this->db->trans_start();

        if (empty($id)) {
            $payload['created_on'] = $date;
            $payload['created_by'] = $user_id;
            $payload['deleted']    = 0;
            $this->db->insert($this->table, $payload);
            $new_id = $this->db->insert_id();
        } else {
            $this->db->where($this->primary_key, $id);
            $this->db->update($this->table, $payload);
            $new_id = $id;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return $new_id;
    }

    /**
     * Soft delete data mata uang
     */
    public function delete_data($id, $user_id = null)
    {
        $payload = [
            'deleted'    => 1,
            'deleted_by' => $user_id,
            'modified_on'=> date('Y-m-d'),
            'modified_by'=> $user_id,
        ];

        $this->db->trans_start();
        $this->db->where($this->primary_key, $id);
        $this->db->update($this->table, $payload);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }

    /**
     * Toggle status aktif / tidak aktif
     */
    public function toggle_activation($id, $user_id = null)
    {
        $current = $this->get_by_id($id);
        if (!$current) {
            return false;
        }

        $new_status = ($current->activation === 'active') ? 'inactive' : 'active';

        $payload = [
            'activation' => $new_status,
            'modified_on'=> date('Y-m-d'),
            'modified_by'=> $user_id,
        ];

        $this->db->trans_start();
        $this->db->where($this->primary_key, $id);
        $this->db->update($this->table, $payload);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return $new_status;
    }
}
