<?php

namespace App\Exports;

class ReportsExport
{
    protected $reports;

    public function __construct($reports)
    {
        $this->reports = $reports;
    }

    public function export()
    {
        \Excel::create('Combined-Report', function($excel) {
            
            // Fleet Performance Sheet
            $excel->sheet('Fleet Performance', function($sheet) {
                $data = [['Truck ID', 'Total Pickups', 'Drivers Present', 'Issues Reported']];
                
                foreach ($this->reports['fleet'] as $item) {
                    $data[] = [
                        $item->truck_id,
                        $item->total_pickups,
                        $item->drivers_present,
                        $item->issues_reported,
                    ];
                }
                
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                });
            });
            
            // Collection Efficiency Sheet
            $excel->sheet('Collection Efficiency', function($sheet) {
                $collection = $this->reports['collection'];
                $completionRate = $collection->total_pickups > 0 
                    ? round(($collection->completed_pickups / $collection->total_pickups) * 100, 2) . '%'
                    : '0%';
                
                $data = [
                    ['Total Pickups', 'Completed Pickups', 'Missed Pickups', 'Completion Rate'],
                    [
                        $collection->total_pickups ?? 0,
                        $collection->completed_pickups ?? 0,
                        $collection->missed_pickups ?? 0,
                        $completionRate,
                    ]
                ];
                
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                });
            });
            
            // Resident Issues Sheet
            $excel->sheet('Resident Issues', function($sheet) {
                $data = [['Issue Type', 'Other Issue', 'Location', 'Incident Date/Time', 'Priority', 'Description']];
                
                foreach ($this->reports['residentIssues'] as $item) {
                    $data[] = [
                        $item->issue_type ?? '',
                        $item->other_issue ?? '',
                        $item->location ?? '',
                        $item->incident_datetime ?? '',
                        $item->priority ?? '',
                        $item->description ?? '',
                    ];
                }
                
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                });
            });
            
            // Driver Issues Sheet
            $excel->sheet('Driver Issues', function($sheet) {
                $data = [['Driver ID', 'Issue Type', 'Description', 'Created At']];
                
                foreach ($this->reports['driverIssues'] as $item) {
                    $data[] = [
                        $item->driver_id ?? '',
                        $item->issue_type ?? '',
                        $item->description ?? '',
                        $item->created_at ?? '',
                    ];
                }
                
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                });
            });
            
            // Waste Statistics Sheet
            $excel->sheet('Waste Statistics', function($sheet) {
                $data = [
                    ['Metric', 'Value'],
                    ['Today Total (kg)', $this->reports['todayTotal'] ?? 0],
                    ['Month Total (kg)', $this->reports['monthTotal'] ?? 0],
                    ['Total Collections', $this->reports['totalCollections'] ?? 0],
                    ['Trucks Used', $this->reports['environment']->trucks_used ?? 0],
                    ['Total Pickups (Environment)', $this->reports['environment']->total_pickups ?? 0],
                ];
                
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                });
            });
            
        })->download('xlsx');
    }
}