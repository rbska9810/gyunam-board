<?php
$sub_menu = "200800";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : G5_TIME_YMD;
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : G5_TIME_YMD;

$g5['title'] = 'OS별 접속자집계';
include_once('./visit.sub.php');

// [데이터 가공 로직]
$max = 0;
$sum_count = 0;
$arr = array();

$sql = " select * from {$g5['visit_table']}
         where vi_date between '$fr_date' and '$to_date' ";
$result = sql_query($sql);
while ($row=sql_fetch_array($result)) {
    $s = $row['vi_os'];
    if(!$s) $s = get_os($row['vi_agent']);
    if(!$s) $s = '기타'; // 빈 값 처리

    if( isset($arr[$s]) ){
        $arr[$s]++;
    } else {
        $arr[$s] = 1;
    }

    if ($arr[$s] > $max) $max = $arr[$s];
    $sum_count++;
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

    /* 1. 리스트 헤더 */
    .vst-list-header {
        display: flex;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
        font-weight: 700;
        color: #888;
        font-size: 13px;
        margin-bottom: 10px;
        border: 1px solid #eee;
    }

    /* 2. 리스트 로우 (행) */
    .vst-list-row {
        display: flex;
        align-items: center;
        padding: 16px 15px;
        border-bottom: 1px solid #f1f1f1;
        font-size: 14px;
        color: #333;
        transition: 0.2s;
    }
    .vst-list-row:hover { background: #fcfcfc; }
    .vst-list-row:last-child { border-bottom: none; }

    /* 컬럼 스타일 */
    .col-rank { width: 60px; text-align: center; font-weight: 700; color: #3235cd; }
    
    .col-os { 
        flex: 1; 
        padding-left: 10px; 
        font-weight: 600; 
        color: #333;
        display: flex; align-items: center;
    }

    .col-graph { width: 30%; padding: 0 20px; }
    .col-count { width: 100px; text-align: right; font-weight: 700; color: #333; }
    .col-rate { width: 80px; text-align: right; color: #888; font-size: 13px; }

    /* 순위 뱃지 (1,2,3위 강조) */
    .rank-badge {
        display: inline-block;
        width: 28px;
        height: 28px;
        line-height: 28px;
        border-radius: 8px;
        background: #f1f3f5;
        color: #666;
        font-size: 13px;
        text-align: center;
    }
    .rank-1 { background: #3235cd; color: #fff; box-shadow: 0 3px 10px rgba(50, 53, 205, 0.3); }
    .rank-2 { background: #5c7cfa; color: #fff; }
    .rank-3 { background: #748ffc; color: #fff; }

    /* OS 이름 뱃지 */
    .os-badge {
        padding: 4px 10px;
        border-radius: 6px;
        background: #f8f9fa;
        color: #555;
        font-size: 13px;
        border: 1px solid #eee;
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
        transition: width 1s ease;
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
        border: 1px solid #eee;
    }

    /* 반응형 */
    @media (max-width: 768px) {
        .vst-layout-container { padding: 20px 15px; }
        .vst-list-header { display: none; } /* 헤더 숨김 */
        
        .col-graph { display: none; } /* 모바일에서 그래프 숨김 */
        .col-rank { width: 40px; }
        .col-count { width: auto; flex: 0 0 80px; }
        .col-rate { width: auto; flex: 0 0 60px; }
    }
</style>

<div class="vst-layout-container">
    
    <div class="vst-list-header">
        <div class="col-rank">순위</div>
        <div class="col-os">OS</div>
        <div class="col-graph">비율 그래프</div>
        <div class="col-count">접속자 수</div>
        <div class="col-rate">비율(%)</div>
    </div>

    <?php
    $i = 0;
    $k = 0;
    $save_count = -1;
    $tot_count = 0;
    $has_data = false;

    if (count($arr)) {
        arsort($arr);
        foreach ($arr as $key=>$value) {
            $has_data = true;
            $count = $arr[$key];
            
            // 순위 계산
            if ($save_count != $count) {
                $i++;
                $no = $i;
                $save_count = $count;
            } else {
                $no = '';
            }

            // 'Unknown' 텍스트 처리 (한글 '기타'로 통일)
            if (!$key || $key == 'Unknown') {
                $key = '기타';
            }

            $rate = ($sum_count > 0) ? ($count / $sum_count * 100) : 0;
            $s_rate = number_format($rate, 1);
            
            // 1~3위 강조 클래스
            $rank_class = '';
            if($no == 1) $rank_class = 'rank-1';
            else if($no == 2) $rank_class = 'rank-2';
            else if($no == 3) $rank_class = 'rank-3';
    ?>
    <div class="vst-list-row">
        <div class="col-rank">
            <?php if($no) { ?>
                <span class="rank-badge <?php echo $rank_class ?>"><?php echo $no ?></span>
            <?php } ?>
        </div>
        
        <div class="col-os">
            <span class="os-badge"><?php echo $key ?></span>
        </div>
        
        <div class="col-graph">
            <div class="vst-progress-bg">
                <div class="vst-progress-fill" style="width: <?php echo $s_rate ?>%;"></div>
            </div>
        </div>

        <div class="col-count">
            <?php echo number_format($count) ?>
        </div>
        
        <div class="col-rate">
            <?php echo $s_rate ?>%
        </div>
    </div>
    <?php 
        } // end foreach
    } // end if
    ?>

    <?php if(!$has_data) { ?>
    <div style="text-align:center; padding:80px 0; color:#999;">
        <span style="display:block; font-size:40px; margin-bottom:10px;">💻</span>
        데이터가 없습니다.
    </div>
    <?php } ?>

    <div class="vst-list-footer">
        <div class="col-rank"></div>
        <div class="col-os">전체 합계</div>
        <div class="col-graph"></div>
        <div class="col-count"><?php echo number_format($sum_count) ?></div>
        <div class="col-rate">100%</div>
    </div>

</div>

<?php
include_once('./admin.tail.php');
?>