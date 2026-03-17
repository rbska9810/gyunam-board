<?php
$sub_menu = "200800";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : G5_TIME_YMD;
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : G5_TIME_YMD;

$g5['title'] = '요일별 접속자집계';
include_once('./visit.sub.php');

// [데이터 가공 로직]
$weekday_str = array('월', '화', '수', '목', '금', '토', '일');
$sum_count = 0;
$max_count = 0;
$arr = array();

// 요일별 데이터 조회
$sql = " select WEEKDAY(vs_date) as weekday_date, SUM(vs_count) as cnt
            from {$g5['visit_sum_table']}
            where vs_date between '{$fr_date}' and '{$to_date}'
            group by weekday_date
            order by weekday_date ";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++) {
    $arr[$row['weekday_date']] = $row['cnt'];
    $sum_count += $row['cnt'];
    if ($row['cnt'] > $max_count) $max_count = $row['cnt'];
}
?>

<style>
    /* 전체 래퍼 */
    .new-admin-wrapper {
        font-family: "Pretendard Variable", sans-serif !important;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.04);
        padding: 30px;
        margin-top: 20px;
        color: #333;
    }

    /* 1. 상단 그래프 영역 */
    .new-chart-container {
        display: flex;
        justify-content: space-around;
        align-items: flex-end;
        height: 240px;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f1f3f5;
        overflow-x: auto;
    }

    .new-chart-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        min-width: 40px;
        position: relative;
        cursor: pointer;
    }

    .new-bar-value {
        font-size: 13px;
        font-weight: 700;
        color: #3235cd;
        margin-bottom: 6px;
        opacity: 0;
        animation: newFadeUp 0.6s forwards;
    }
    .new-bar-stick {
        width: 28px;
        background: #e9ecef;
        border-radius: 8px 8px 0 0;
        height: 0; 
        transition: height 1s cubic-bezier(0.25, 0.8, 0.25, 1);
        min-height: 4px;
    }
    .new-bar-stick.highlight {
        background: #3235cd; 
        box-shadow: 0 4px 12px rgba(50, 53, 205, 0.3);
    }
    .new-chart-item:hover .new-bar-stick {
        opacity: 0.8;
        transform: scaleY(1.05);
    }
    .new-bar-label { margin-top: 10px; font-size: 14px; font-weight: 600; color: #666; }

    /* 2. 리스트 영역 (DIV 구조) */
    .new-list-header {
        display: flex;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
        font-weight: 600;
        color: #888;
        font-size: 13px;
        margin-bottom: 10px;
    }
    
    .new-list-row {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #f1f1f1;
        font-size: 14px;
        color: #333;
        transition: 0.2s;
    }
    .new-list-row:hover { background: #fcfcfc; }
    .new-list-row:last-child { border-bottom: none; }

    /* 컬럼 너비 설정 (Flex) */
    .col-day { width: 15%; min-width: 80px; }
    .col-graph { flex: 1; padding: 0 20px; }
    .col-count { width: 20%; min-width: 100px; font-weight: 700; color: #333; text-align: right; }
    .col-rate { width: 15%; min-width: 80px; color: #888; text-align: right; }

    /* 요일 뱃지 */
    .new-day-badge {
        display: inline-block;
        width: 32px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        background: #f1f3f5;
        border-radius: 50%;
        color: #555;
        font-weight: 700;
        margin-right: 10px;
        font-size: 13px;
    }
    .new-day-badge.sunday { color: #e03131; background: #fff5f5; }
    .new-day-badge.saturday { color: #1971c2; background: #e7f5ff; }

    /* 가로 바 그래프 */
    .new-progress-bg {
        width: 100%;
        height: 8px;
        background: #f1f3f5;
        border-radius: 4px;
        overflow: hidden;
    }
    .new-progress-fill {
        height: 100%;
        background: #3235cd;
        border-radius: 4px;
    }

    /* 합계 행 */
    .new-list-footer {
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

    @keyframes newFadeUp { to { opacity: 1; transform: translateY(0); } }

    /* 반응형 */
    @media (max-width: 768px) {
        .new-admin-wrapper { padding: 20px 15px; border-radius: 15px; }
        .new-chart-container { height: 180px; }
        .new-bar-stick { width: 16px; border-radius: 4px 4px 0 0; }
        .col-day { width: auto; min-width: 60px; }
        .col-graph { display: none; } /* 모바일에서 그래프 숨김 */
        .col-count { flex: 1; text-align: center; }
        .col-rate { width: auto; }
        
        .new-list-header { display: none; } /* 모바일에서 헤더 숨김 (깔끔하게) */
        .new-list-row { flex-wrap: wrap; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .col-count { text-align: right; font-size: 15px; }
    }
</style>

<div class="new-admin-wrapper">
    
    <div class="new-chart-container">
        <?php
        for ($i=0; $i<7; $i++) {
            $count = isset($arr[$i]) ? (int) $arr[$i] : 0;
            $height_rate = ($max_count > 0) ? round(($count / $max_count) * 100) : 0;
            if($count > 0 && $height_rate < 5) $height_rate = 5;
            $highlight = ($count > 0 && $count == $max_count) ? 'highlight' : '';
        ?>
        <div class="new-chart-item">
            <div class="new-bar-value" style="animation-delay: <?php echo $i*0.1 ?>s"><?php echo number_format($count) ?></div>
            <div class="new-bar-stick <?php echo $highlight ?>" style="height: <?php echo $height_rate ?>%;"></div>
            <div class="new-bar-label"><?php echo $weekday_str[$i] ?></div>
        </div>
        <?php } ?>
    </div>

    <div class="new-list-wrap">
        <div class="new-list-header">
            <div class="col-day">요일</div>
            <div class="col-graph">접속 비율</div>
            <div class="col-count">접속자 수</div>
            <div class="col-rate">비율(%)</div>
        </div>

        <?php
        $has_data = false;
        for ($i=0; $i<7; $i++) {
            $count = isset($arr[$i]) ? (int) $arr[$i] : 0;
            $rate = ($sum_count > 0) ? ($count / $sum_count * 100) : 0;
            $s_rate = number_format($rate, 1);
            if($count > 0) $has_data = true;

            $day_class = '';
            if($i == 5) $day_class = 'saturday'; 
            if($i == 6) $day_class = 'sunday';   
        ?>
        <div class="new-list-row">
            <div class="col-day">
                <span class="new-day-badge <?php echo $day_class ?>"><?php echo $weekday_str[$i] ?></span>
                <span style="font-weight:600; font-size:14px; color:#333; display:none;">요일</span> </div>
            <div class="col-graph">
                <div class="new-progress-bg">
                    <div class="new-progress-fill" style="width: <?php echo $s_rate ?>%;"></div>
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

        <div class="new-list-footer">
            <div class="col-day">전체 합계</div>
            <div class="col-graph"></div>
            <div class="col-count"><?php echo number_format($sum_count) ?>명</div>
            <div class="col-rate">100%</div>
        </div>
    </div>
</div>

<?php
include_once('./admin.tail.php');
?>