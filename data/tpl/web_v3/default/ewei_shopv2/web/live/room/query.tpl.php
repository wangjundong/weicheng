<?php defined('IN_IA') or exit('Access Denied');?>
<style type="text/css">
    .popover{z-index:10000;}
    .alert{margin-top:10px;}
</style>
<div style='max-height:500px;overflow:auto;min-width:850px;'>
    <div class="alert alert-info">提示：直播商品可在直播中显示，且观看直播的用户可在直播中自由购买已添加的商品。</div>
    <table class="table">
        <thead>
        <tr>
            <td>商品</td>
            <td style="width:100px;text-align: center;">商品价格</td>
            <td style="width:100px;text-align: center;">库存</td>
            <th style="width:100px;text-align: center;">操作</th>
        </tr>
        </thead>
        <tbody class="ui-sortable container">
        <?php  if(is_array($goods)) { foreach($goods as $row) { ?>
        <tr>
            <td>
                <img src="<?php  echo tomedia($row['thumb'])?>" style='width:30px;height:30px;padding1px;border:1px solid #ccc' /> <?php  echo $row['title'];?>
            </td>
            <td style="text-align: right;">&yen;<?php  echo $row['marketprice'];?></td>
            <td style="text-align: right;"><?php  echo $row['total'];?></td>
            <td style="text-align: center;">
                <a href="javascript:void(0);" class="label label-primary" onclick='biz.selector_new.set(this, <?php  echo json_encode($row);?>)'>选择</a>
            </td>
        </tr>
        <?php  } } ?>
        <?php  if(count($goods)<=0) { ?>
        <tr>
            <td colspan='4' align='center'>未找到商品</td>
        </tr>
        <?php  } ?>
        </tbody>
    </table>
    <div style="text-align:right;width:100%;">
        <?php  echo $pager;?>
    </div>
</div>
<script type="text/javascript">
    require(['bootstrap'], function ($) {
        $('[data-toggle="tooltip"]').tooltip({
            container: $(document.body)
        });
        $('[data-toggle="popover"]').popover({
            container: $(document.body)
        });
    });
    //分页函数
    var type = '';
    function select_page(url,pindex,obj) {
        if(pindex==''||pindex==0){
            return;
        }
        var keyword = $.trim($("#goodsid_input").val());
        $("#goodsid_input").html('<div class="tip">正在进行搜索...</div>');

        $.ajax({
            url:"<?php  echo webUrl('live/room/query')?>",
            type:'get',
            data:{title:keyword,page:pindex},
            async : false, //默认为true 异步
            success:function(data){
                $(".content").html(data);
            }
        });
    }

</script>
