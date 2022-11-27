{include file='admin/tabler_header.tpl'}

<script src="//cdn.jsdelivr.net/npm/jsoneditor@9.9.2/dist/jsoneditor.min.js"></script>
<link href="//cdn.jsdelivr.net/npm/jsoneditor@9.9.2/dist/jsoneditor.min.css" rel="stylesheet" type="text/css">

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">创建节点</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">创建各类节点</span>
                    </div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a id="create-node" href="#" class="btn btn-primary d-none d-sm-inline-block">
                            <i class="icon ti ti-device-floppy"></i>
                            保存
                        </a>
                        <a id="create-node" href="#" class="btn btn-primary d-sm-none btn-icon">
                            <i class="icon ti ti-device-floppy"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header card-header-light">
                            <h3 class="card-title">基础信息</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">名称</label>
                                <div class="col">
                                    <input id="name" type="text" class="form-control" value="">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">连接地址</label>
                                <div class="col">
                                    <textarea id="server" class="col form-control" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">服务器IP</label>
                                <div class="col">
                                    <input id="node_ip" type="text" class="form-control" value="">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">流量倍率</label>
                                <div class="col">
                                    <input id="traffic_rate" type="text" class="form-control"
                                        value="">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">接入类型</label>
                                <div class="col">
                                    <select id="sort" class="col form-select">
                                        <option value="11">V2Ray</option>
                                        <option value="14">Trojan</option>
                                        <option value="0">ShadowSocks</option>
                                        <option value="1">ShadowSocksR</option>
                                        <option value="9">ShadowsocksR 单端口多用户（旧）</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">单端口多用户</label>
                                <div class="col">
                                    <select id="mu_only" class="col form-select">
                                        <option value="-1">只启用普通端口</option>
                                        <option value="1">只启用单端口多用户</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">自定义配置</label>
                                <dev id="custom_config"></dev>
                                <label class="form-label col-3 col-form-label">
                                    请参考 <a href="//wiki.sspanel.org/#/setup-custom-config" target="_blank">wiki.sspanel.org/#/setup-custom-config</a> 修改节点自定义配置
                                </label>
                            </div>
                            <div class="hr-text">
                                <span>高级选项</span>
                            </div>
                            <div class="mb-3">
                                <div class="divide-y">
                                    <div>
                                        <label class="row">
                                            <span class="col">显示此节点</span>
                                            <span class="col-auto">
                                                <label class="form-check form-check-single form-switch">
                                                    <input id="type" class="form-check-input" type="checkbox"
                                                        checked="">
                                                </label>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-header card-header-light">
                            <h3 class="card-title">其他信息</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">公有备注</label>
                                <div class="col">
                                    <input id="info" type="text" class="form-control" value="">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">等级</label>
                                <div class="col">
                                    <input id="node_class" type="text" class="form-control" value="">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">组别</label>
                                <div class="col">
                                    <input id="node_group" type="text" class="form-control" value="">
                                </div>
                            </div>
                            <div class="hr-text">
                                <span>流量设置</span>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">可用流量 (GB)</label>
                                <div class="col">
                                    <input id="node_bandwidth_limit" type="text" class="form-control"
                                        value="">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">流量重置日</label>
                                <div class="col">
                                    <input id="bandwidthlimit_resetday" type="text" class="form-control"
                                        value="">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-3 col-form-label">速率限制 (Mbps)</label>
                                <div class="col">
                                    <input id="node_speedlimit" type="text" class="form-control"
                                        value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('custom_config');
    var options = {
        modes: ['code', 'tree'],
    };
    const editor = new JSONEditor(container, options);
    editor.set({$node->custom_config})

    $("#create-node").click(function() {
        $.ajax({
            url: '/node',
            type: 'POST',
            dataType: "json",
            data: {
                {foreach $update_field as $key}
                {$key}: $('#{$key}').val(),
                {/foreach}
                type: $("#type").is(":checked"),
                custom_config: JSON.stringify(editor.get()),
            },
            success: function(data) {
                if (data.ret == 1) {
                    $('#success-message').text(data.msg);
                    $('#success-dialog').modal('show');
                    window.setTimeout("location.href=top.document.referrer", {$config['jump_delay']});
                } else {
                    $('#fail-message').text(data.msg);
                    $('#fail-dialog').modal('show');
                }
            }
        })
    });
</script>

{include file='admin/tabler_footer.tpl'}