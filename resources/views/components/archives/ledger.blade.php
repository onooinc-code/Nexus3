@props(['title', 'stamp' => null, 'data' => [], 'type' => 'generic'])

<div class="eg-card">
    @if($stamp)
        <div class="dossier-stamp">{{ $stamp }}</div>
    @endif
    
    <div class="eg-section-title">
        <i class="fa-solid fa-paperclip me-2"></i> {{ $title }}
    </div>

    @if(empty($data))
        <div class="text-center p-4 text-muted" style="border: 1px dashed var(--studio-border); background: rgba(0,0,0,0.2);">
            لا توجد سجلات متاحة في هذا الملف حتى الآن.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-borderless ledger-table mb-0">
                <thead>
                    <tr>
                    @if($type === 'hypocrisy')
                        <th>التاريخ (1)</th>
                        <th>التصريح الأول</th>
                        <th>التاريخ (2)</th>
                        <th>الفعل المتناقض</th>
                    @elseif($type === 'broken')
                        <th>تاريخ الوعد</th>
                        <th>تفاصيل الوعد المكسور</th>
                        <th>السياق / النتيجة</th>
                    @elseif($type === 'passive')
                        <th>التاريخ</th>
                        <th>الجملة (النص الظاهري)</th>
                        <th>الترجمة (المعنى المبطن)</th>
                    @elseif($type === 'apology')
                        <th>التاريخ</th>
                        <th>الاعتذار المزيف</th>
                        <th>التحليل (سبب التزييف)</th>
                    @elseif($type === 'vulnerability')
                        <th>التاريخ</th>
                        <th>الزلة (نقطة الضعف)</th>
                        <th>القيمة الاستراتيجية</th>
                    @elseif(in_array($type, ['avoidance', 'opportunistic', 'jealousy']))
                        <th>التاريخ</th>
                        <th colspan="2">تفاصيل الحادثة</th>
                    @elseif($type === 'blackmail')
                        <th>التاريخ</th>
                        <th>العبارة المستخدمة</th>
                        <th>التكتيك (Tactic)</th>
                    @elseif($type === 'excuse')
                        <th>التاريخ</th>
                        <th>العذر الوهمي</th>
                        <th>الحقيقة (Reality)</th>
                    @elseif($type === 'confession')
                        <th>التاريخ</th>
                        <th colspan="2">الاعتراف المتأخر</th>
                    @elseif($type === 'concession')
                        <th>التاريخ</th>
                        <th colspan="2">التنازل الذي قدمته</th>
                    @elseif($type === 'kept')
                        <th>التاريخ</th>
                        <th>الوعد المنفذ</th>
                        <th>السياق</th>
                    @else
                        <th>التاريخ</th>
                        <th colspan="2">التفاصيل</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        @if($type === 'hypocrisy')
                            <td class="date-cell">{{ $row['date1'] ?? '' }}</td>
                            <td>{{ $row['statement1'] ?? '' }}</td>
                            <td class="date-cell">{{ $row['date2'] ?? '' }}</td>
                            <td class="text-danger fw-bold">{{ $row['statement2'] ?? '' }}</td>
                        @elseif($type === 'broken')
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td>{{ $row['promise'] ?? '' }}</td>
                            <td class="text-muted">{{ $row['context'] ?? '' }}</td>
                        @elseif($type === 'passive')
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td>"{{ $row['quote'] ?? '' }}"</td>
                            <td style="color: var(--archive-red);">{{ $row['translation'] ?? '' }}</td>
                        @elseif($type === 'apology')
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td>"{{ $row['quote'] ?? '' }}"</td>
                            <td class="text-danger">{{ $row['analysis'] ?? '' }}</td>
                        @elseif($type === 'vulnerability')
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td>{{ $row['slip'] ?? '' }}</td>
                            <td style="color: var(--archive-red); font-weight: bold;">{{ $row['strategic_value'] ?? '' }}</td>
                        @elseif(in_array($type, ['avoidance', 'opportunistic', 'jealousy', 'concession']))
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td colspan="2">{{ $row['incident'] ?? '' }}</td>
                        @elseif($type === 'blackmail')
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td>"{{ $row['quote'] ?? '' }}"</td>
                            <td class="text-danger" style="font-family: var(--font-typewriter);">{{ $row['tactic'] ?? '' }}</td>
                        @elseif($type === 'excuse')
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td>{{ $row['excuse'] ?? '' }}</td>
                            <td class="text-danger fw-bold">{{ $row['reality'] ?? '' }}</td>
                        @elseif($type === 'confession')
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td colspan="2">{{ $row['confession'] ?? '' }}</td>
                        @elseif($type === 'kept')
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td>{{ $row['promise'] ?? '' }}</td>
                            <td class="text-muted">{{ $row['context'] ?? '' }}</td>
                        @else
                            <td class="date-cell">{{ $row['date'] ?? '' }}</td>
                            <td colspan="2">{{ json_encode($row, JSON_UNESCAPED_UNICODE) }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    @endif
</div>
