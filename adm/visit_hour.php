<?php
$sub_menu = "200800";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : G5_TIME_YMD;
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : G5_TIME_YMD;

$g5['title'] = '시간별 접속자집계';
include_once('./visit.sub.php');

// [데이터 가공 로직]
$max = 0;
$sum_count = 0;
$arr = array();

$sql = " select SUBSTRING(vi_time,1,2) as vi_hour, count(vi_id) as cnt
            from {$g5['visit_table']}
            where vi_date between '{$fr_date}' and '{$to_date}'
            group by vi_hour
            order by vi_hour ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $arr[$row['vi_hour']] = $row['cnt'];
    if ($row['cnt'] > $max) $max = $row['cnt'];
    $sum_count += $row['cnt'];
}
?>

<style>
    /* 전체 래퍼 */
    .vst-layout-container {
        font-family: "Pretendard Variable", sans-serif !important;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.04);
        padding: 30px;
        margin-top: 20px;
        color: #333;
    }

    /* 1. 상단 그래프 영역 (가로 스크롤) */
    .vst-chart-wrap {
        display: flex;
        justify-content: flex-start; /* 왼쪽 정렬 (데이터 많음) */
        align-items: flex-end;
        height: 260px;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f1f3f5;
        overflow-x: auto; /* 가로 스크롤 필수 */
        gap: 15px; /* 간격 */
        padding-left: 10px;
        padding-right: 10px;
    }
    .vst-chart-wrap::-webkit-scrollbar { height: 8px; }
    .vst-chart-wrap::-webkit-scrollbar-thumb { background: #e9ecef; border-radius: 4px; }

    .vst-chart-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 30px; /* 최소 너비 */
        position: relative;
        cursor: pointer;
        flex-shrink: 0;
    }

    .vst-bar-value {
        font-size: 11px;
        font-weight: 700;
        color: #3235cd;
        margin-bottom: 6px;
        opacity: 0;
        animation: vstFadeUp 0.6s forwards;
    }
    .vst-bar-stick {
        width: 14px; /* 얇은 막대 */
        background: #e9ecef;
        border-radius: 4px 4px 0 0;
        height: 0; 
        transition: height 1s cubic-bezier(0.25, 0.8, 0.25, 1);
        min-height: 4px;
    }
    .vst-bar-stick.highlight {
        background: #3235cd; 
        box-shadow: 0 4px 12px rgba(50, 53, 205, 0.3);
    }
    .vst-chart-item:hover .vst-bar-stick {
        opacity: 0.8;
        transform: scaleY(1.05);
        background: #3235cd;
    }
    .vst-bar-label { margin-top: 10px; font-size: 12px; font-weight: 600; color: #888; }

    /* 2. 리스트 영역 (Flex DIV 구조) */
    .vst-list-header {
        display: flex;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
        font-weight: 600;
        color: #888;
        font-size: 13px;
        margin-bottom: 10px;
    }
    
    .vst-list-row {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #f1f1f1;
        font-size: 14px;
        color: #333;
        transition: 0.2s;
    }
    .vst-list-row:hover { background: #fcfcfc; }
    .vst-list-row:last-child { border-bottom: none; }

    /* 컬럼 너비 설정 */
    .col-time { width: 15%; min-width: 80px; font-weight: 700; color: #555; }
    .col-graph { flex: 1; padding: 0 20px; }
    .col-count { width: 20%; min-width: 100px; font-weight: 700; color: #333; text-align: right; }
    .col-rate { width: 15%; min-width: 80px; color: #888; text-align: right; }

    /* 시간 뱃지 */
    .vst-time-badge {
        display: inline-block;
        padding: 4px 8px;
        background: #f1f3f5;
        border-radius: 6px;
        color: #555;
        font-size: 12px;
    }

    /* 가로 바 그래프 */
    .vst-progress-bg {
        width: 100%;
        height: 6px;
        background: #f1f3f5;
        border-radius: 3px;
        overflow: hidden;
    }
    .vst-progress-fill {
        height: 100%;
        background: #3235cd;
        border-radius: 3px;
    }

    /* 합계 행 */
    .vst-list-footer {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
        margin-top: 10px;
        font-weight: 800;
        color: #3235cd;
        font-size: 15px;
    }

    @keyframes vstFadeUp { to { opacity: 1; transform: translateY(0); } }

    /* 반응형 */
    @media (max-width: 768px) {
        .vst-layout-container { padding: 20px 15px; border-radius: 15px; }
        .vst-chart-wrap { height: 200px; gap: 8px; }
        .vst-bar-stick { width: 10px; }
        .vst-bar-value { font-size: 10px; }
        
        .col-graph { display: none; } /* 모바일에서 그래프 바 숨김 */
        .vst-list-header { display: none; }
        .vst-list-row { flex-wrap: wrap; justify-content: space-between; }
        .col-count { text-align: right; }
        .col-time { width: auto; }
    }
</style>

<div class="vst-layout-container">
    
    <div class="vst-chart-wrap">
        <?php
        for ($i=0; $i<24; $i++) {
            $hour = sprintf("%02d", $i);
            $count = isset($arr[$hour]) ? (int) $arr[$hour] : 0;
            
            // 높이 계산
            $height_rate = ($max > 0) ? round(($count / $max) * 100) : 0;
            if($count > 0 && $height_rate < 5) $height_rate = 5;
            
            $highlight = ($count > 0 && $count == $max) ? 'highlight' : '';
        ?>
        <div class="vst-chart-item">
            <div class="vst-bar-value" style="animation-delay: <?php echo $i*0.05 ?>s">
                <?php echo ($count > 0) ? number_format($count) : ''; ?>
            </div>
            <div class="vst-bar-stick <?php echo $highlight ?>" style="height: <?php echo $height_rate ?>%;"></div>
            <div class="vst-bar-label"><?php echo $i ?>시</div>
        </div>
        <?php } ?>
    </div>

    <div class="vst-list-wrap">
        <div class="vst-list-header">
            <div class="col-time">시간</div>
            <div class="col-graph">접속 비율</div>
            <div class="col-count">접속자 수</div>
            <div class="col-rate">비율(%)</div>
        </div>

        <?php
        $has_data = false;
        for ($i=0; $i<24; $i++) {
            $hour = sprintf("%02d", $i);
            $count = isset($arr[$hour]) ? (int) $arr[$hour] : 0;
            
            $rate = ($sum_count > 0) ? ($count / $sum_count * 100) : 0;
            $s_rate = number_format($rate, 1);
            
            if($count > 0) $has_data = true;
            
            // 강조 시간대 (근무시간 등) 색상 다르게 줄 수도 있음 (현재는 통일)
        ?>
        <div class="vst-list-row">
            <div class="col-time">
                <span class="vst-time-badge"><?php echo $hour ?>시</span>
            </div>
            <div class="col-graph">
                <div class="vst-progress-bg">
                    <div class="vst-progress-fill" style="width: <?php echo $s_rate ?>%;"></div>
                </div>
            </div>
            <div class="col-count">
                <?php echo number_format($count) ?>명
            </div>
            <div class="col-rate">
                <?php echo $s_rate ?>%
            </div>
        </div>
        <?php } ?>

        <?php if(!$has_data) { ?>
        <div style="text-align:center; padding:50px 0; color:#999;">데이터가 없습니다.</div>
        <?php } ?>

        <div class="vst-list-footer">
            <div class="col-time">전체 합계</div>
            <div class="col-graph"></div>
            <div class="col-count"><?php echo number_format($sum_count) ?>명</div>
            <div class="col-rate">100%</div>
        </div>
    </div>
</div>

<?php
include_once('./admin.tail.php');
?>