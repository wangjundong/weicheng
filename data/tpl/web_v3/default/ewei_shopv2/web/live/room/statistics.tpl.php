<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_header', TEMPLATE_INCLUDEPATH)) : (include template('_header', TEMPLATE_INCLUDEPATH));?>

<style>
    .btn-default.disabled {color: #333;}  .alert-danger {display: none;} .col-md-4, .col-md-6 {height: 100px; padding-top: 30px;}
    .progress { height: 10px; margin: 0;margin-top: 4px;overflow: hidden;background-color: #ffffff; border-radius: 0px;box-shadow: none;}
    .progress-bar{animation: animate-positive 2s;}
    .progress-num{float: left;margin-right:15px;color: #999;}
    .statistics-ul{}
    .statistics-ul li{font-size:12px;height:30px;margin:5px 0;}
    .statistics-ul li.active a{color:#fff;background: #42c3c3;}
    .statistics-ul li a{color:#424242;display: block;height:30px;line-height: 30px;text-align: center;background: #f7f7f7;}
    .ibox-content h2 i{font-size:12px;font-style: normal;}
</style>

<div class="page-header">
    当前位置：<span class="text-primary">【<?php  echo $room['title'];?>】直播间统计</span>
</div>

<div class="page-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins" style="border: 1px solid #e7eaec">
                <div class="ibox-title">
                    <h5>直播间礼品统计</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-6 text-center" style="width:20%;">
                            领取优惠券
                            <h2 class="no-margins"><?php  echo $room['coupon_num'];?> <i>张</i></h2>
                        </div>
                        <div class="col-md-4 text-center" style="width:20%;">
                            推送优惠券
                            <h2 class="no-margins"><?php  echo $coupontotal;?> <i>张</i></h2>
                        </div>
                        <div class="col-md-4 text-center" style="width:20%;">
                            推送红包
                            <h2 class="no-margins"><?php  echo $redpacktotal;?> <i>个</i></h2>
                        </div>
                        <div class="col-md-4 text-center" style="width:20%;">
                            推送红包金额
                            <h2 class="no-margins"><?php  echo price_format($redpacktprice,2)?> <i>元</i></h2>
                        </div>
                        <div class="col-md-6 text-center" style="width:20%;">
                            访问量
                            <h2 class="no-margins"><?php  echo $room['visit'];?> <i>次</i></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>
        <div class="form-group live-url">
            <?php  if(!empty($statistics_time)) { ?>
            <label class="col-sm-3 control-label" style="padding-right:0;">
                <ul class="statistics-ul">
                    <?php  if(is_array($statistics_time)) { foreach($statistics_time as $index => $row) { ?>
                    <li class="<?php  if(($index==0 && empty($_GPC['start'])) || $_GPC['start']==$row['starttime']) { ?>active<?php  } ?>" data-start="<?php  echo $row['starttime'];?>" data-end="<?php  echo $row['endtime'];?>" data-roomid="<?php  echo $row['roomid'];?>">
                        <a href="<?php  echo webUrl('live/room/statistics',array('roomid'=>$row['roomid'],'start'=>$row['starttime'],'end'=>$row['endtime']));?>"><?php  echo date('y/m/d H:i',$row['starttime'])?>-<?php  echo date('m/d H:i',$row['endtime'])?></a>
                    </li>
                    <?php  } } ?>
                </ul>
            </label>
            <div class="col-sm-9 col-xs-12">
                <div class="panel-body">
                    <?php  if(empty($list)) { ?>
                    <div class='panel panel-default' style="margin:0 15px;">
                        <div class='panel-body' style='text-align: center;padding:30px;'>
                            暂时没有任何数据!
                        </div>
                    </div>
                    <?php  } else { ?>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th style='width:100px;'>
                                商品
                            </th>
                            <th style='width:60px;'>交易量</th>
                            <th style="width:68px;">所占比例</th>
                            <th></th>
                            <th style='width:100px;'>交易额</th>
                            <th>所占比例</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php  if(is_array($list)) { foreach($list as $row) { ?>
                        <tr>
                            <td><?php  echo $row['title'];?></td>
                            <td><?php  echo $row['total'];?></td>
                            <td><span class="process-num" style="color:#000"><?php  echo $row['totalpro'];?>%</span></td>
                            <td>
                                <div class="progress" style='max-width:500px;' >
                                    <div style="width: <?php  echo $row['totalpro'];?>%;" class="progress-bar progress-bar-info" ></div>
                                </div>
                            </td>
                            <td>&yen;<?php  echo $row['price'];?></td>
                            <td><span class="process-num" style="color:#000"><?php  echo $row['pricepro'];?>%</span></td>
                            <td>
                                <div class="progress" style='max-width:500px;' >
                                    <div style="width: <?php  echo $row['pricepro'];?>%;" class="progress-bar progress-bar-info" ></div>
                                </div>
                            </td>
                        </tr>
                        <?php  } } ?>
                        </tbody>
                    </table>
                    <?php  } ?>
                </div>
            </div>
            <?php  } else { ?>
            <div class='panel panel-default' style="margin:0 15px;">
                <div class='panel-body' style='text-align: center;padding:30px;'>
                    暂时没有任何数据!
                </div>
            </div>
            <?php  } ?>
        </div>
    </div>
    <a href="<?php  echo webUrl('live/room')?>" class="btn btn-default btn-sm">返回列表</a>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('_footer', TEMPLATE_INCLUDEPATH)) : (include template('_footer', TEMPLATE_INCLUDEPATH));?>
