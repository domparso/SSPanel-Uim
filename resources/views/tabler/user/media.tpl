{include file='user/header.tpl'}

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">限制站点解锁</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">你可以在这里查看节点的限制站点的解锁情况</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>节点</th>
                                        {if count($results) > 0}
                                            {$keyList = array_keys($results['0']['unlock_item'])}
                                            {foreach $results['0']['unlock_item'] as $key => $value}
                                                {if $key !== 'BilibiliChinaMainland'}
                                                    {if $key === 'BilibiliHKMCTW'}
                                                        <th>Bilibili（港澳台）</th>
                                                    {elseif $key === 'BilibiliTW'}
                                                        <th>Bilibili（台湾）</th>
                                                    {else}
                                                        <th>{$key}</th>
                                                    {/if}
                                                {/if}
                                            {/foreach}
                                        {/if}
                                        <th>更新时间</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $results as $result}
                                        <tr>
                                            <td>{$result['node_name']}</td>
                                            {foreach $keyList as $key}
                                                <td>{$result['unlock_item'][$key]}</td>
                                            {/foreach}
                                            <td>{date('Y-m-d H:i:s', $result['created_at'])}</td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
{include file='user/footer.tpl'}