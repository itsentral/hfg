<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Logs extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('logs/Logs_model');
        $this->template->title('System Activity Logs');
        $this->template->page_icon('fa fa-history');
    }

    public function index()
    {
        $today = date('Y-m-d');
        $data = [
            'title'       => 'System Activity Logs',
            'modules'     => $this->Logs_model->get_distinct_modules(),
            'start_date'  => $today,
            'end_date'    => $today,
        ];
        $this->template->render('index', $data);
    }

    /**
     * Server-side data endpoint for ag-Grid (Infinite Row Model)
     */
    public function get_data()
    {
        $startRow  = (int) $this->input->post('startRow');
        $endRow    = (int) $this->input->post('endRow');
        $limit     = ($endRow > $startRow) ? ($endRow - $startRow) : 50;
        $offset    = $startRow;

        $filters = [
            'start_date' => $this->input->post('start_date', true) ?: date('Y-m-d'),
            'end_date'   => $this->input->post('end_date', true) ?: date('Y-m-d'),
            'module'     => $this->input->post('module', true),
            'status'     => $this->input->post('status', true),
            'search'     => $this->input->post('search', true),
        ];

        $sort = [
            'colId' => $this->input->post('sortCol', true),
            'sort'  => $this->input->post('sortDir', true),
        ];

        $rows       = $this->Logs_model->get_logs($filters, $limit, $offset, $sort);
        $totalCount = $this->Logs_model->count_logs($filters);

        // Sanitize & format rows for ag-Grid
        $formatted = [];
        foreach ($rows as $row) {
            $statusText = ((int)$row->status === 1) ? 'SUCCESS' : 'FAILED';
            $userDisplay = !empty($row->username) 
                ? $row->username 
                : (!empty($row->created_by) ? 'User #' . $row->created_by : 'System');

            $formatted[] = [
                'id'         => (int) $row->id,
                'created_at' => $row->created_at,
                'user_name'  => $userDisplay,
                'module'     => $row->module,
                'action'     => !empty($row->methode) ? $row->methode : '-',
                'message'    => $row->message,
                'status'     => $statusText,
                'ip_address' => $row->ip_address,
                'platform'   => $row->platform,
                'agent'      => $row->agent,
                'has_data'   => !empty($row->data),
                'has_query'  => !empty($row->sql),
            ];
        }

        echo json_encode([
            'rows'       => $formatted,
            'totalCount' => $totalCount,
        ]);
        exit;
    }

    /**
     * Fetch single log full details for modal viewer
     */
    public function detail($id = null)
    {
        $id = (int) $id;
        if (!$id) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid ID']);
            exit;
        }

        $log = $this->Logs_model->get_log_by_id($id);
        if (!$log) {
            echo json_encode(['status' => 0, 'msg' => 'Log not found']);
            exit;
        }

        // Format data json if possible
        $dataFormatted = '';
        if (!empty($log->data)) {
            $decoded = json_decode($log->data, true);
            $dataFormatted = ($decoded !== null) 
                ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) 
                : $log->data;
        }

        $statusText = ((int)$log->status === 1) ? 'SUCCESS' : 'FAILED';
        $userDisplay = !empty($log->username) 
            ? $log->username 
            : (!empty($log->created_by) ? 'User #' . $log->created_by : 'System');

        echo json_encode([
            'status' => 1,
            'data'   => [
                'id'         => $log->id,
                'created_at' => $log->created_at,
                'user_name'  => $userDisplay,
                'user_id'    => $log->created_by,
                'module'     => $log->module,
                'action'     => !empty($log->methode) ? $log->methode : '-',
                'message'    => $log->message,
                'status'     => $statusText,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->agent,
                'platform'   => $log->platform,
                'query_sql'  => $log->sql,
                'data_json'  => $dataFormatted,
            ]
        ]);
        exit;
    }

    /**
     * Export logs to CSV with all detailed fields (SQL, Data JSON, Client info)
     */
    public function export_csv()
    {
        $filters = [
            'start_date' => $this->input->get('start_date', true) ?: date('Y-m-d'),
            'end_date'   => $this->input->get('end_date', true) ?: date('Y-m-d'),
            'module'     => $this->input->get('module', true),
            'status'     => $this->input->get('status', true),
            'search'     => $this->input->get('search', true),
        ];

        $sort = [
            'colId' => 'id',
            'sort'  => 'desc',
        ];

        // Retrieve up to 20,000 records for CSV export
        $rows = $this->Logs_model->get_logs($filters, 20000, 0, $sort);

        $filename = 'activity_logs_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header column definitions
        fputcsv($out, [
            'Log ID',
            'Timestamp',
            'User',
            'Module',
            'Action',
            'Status',
            'Message',
            'IP Address',
            'Platform',
            'User Agent',
            'Executed Query (SQL)',
            'Payload / Data (JSON)',
        ]);

        foreach ($rows as $r) {
            $stText = ((int)$r->status === 1) ? 'SUCCESS' : 'FAILED';
            $userDisp = !empty($r->username) 
                ? $r->username 
                : (!empty($r->created_by) ? 'User #' . $r->created_by : 'System');

            fputcsv($out, [
                $r->id,
                $r->created_at,
                $userDisp,
                $r->module,
                $r->methode,
                $stText,
                $r->message,
                $r->ip_address,
                $r->platform,
                $r->agent,
                $r->sql,
                $r->data,
            ]);
        }

        fclose($out);
        exit;
    }
}
