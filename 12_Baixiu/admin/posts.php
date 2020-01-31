<?php 
  
  require_once '../functions.php';

  bx_get_current_user();
  // 分类处理
  // http://baixiu.io/admin/posts.php?category=3&status=published

  $where = '1 = 1';
  $search = '';
  // 分类筛选 如果有晒选就添加到所有文章查询语句中
  if (isset($_GET['category']) && $_GET['category']!=='all') {
     $where.=' and posts.category_id='.$_GET['category'];
     $search.= '&category='.$_GET['category'];
  }
  
  // 状态筛选
  if (isset($_GET['status']) && $_GET['status']!=='all') {
     $where.= " and posts.status='{$_GET['status']}'" ;
     $search.= '&status='.$_GET['status'];
  }
  // 查询出来所有的页数
  // 页数 => ceil(totalpage/pagesize)
  $page_size = 20;
  $skip = 0;
  $page = empty($_GET['page'])? 1: (int)$_GET['page'];
  if ($page < 1) {
     header('Location: /admin/posts.php?page=1'.$search);
  }
  $sql = "select 
      count(1) as count 
      from posts
      inner join categories on posts.category_id = categories.id
      inner join users on posts.user_id = users.id
      where ${where};"; 
  $total_counts = (int)bx_fetch_one($sql)['count'];

  $total_pages = (int)ceil($total_counts/$page_size);
  // 如果查询出结果是 0 则从定向到初始状态 /admin/posts.php?page=1
  if ($total_pages < 1) {
     header('Location: /admin/posts.php?page=1');
     exit();
  }
  if ($page > $total_pages) {
     header('Location: /admin/posts.php?page='.$total_pages.$search);
  }
  // 处理分页参数
  $skip = ($page - 1)*$page_size;
  // 查询分类
  // 获取所有的文章
  // 关联查询
  $posts =  bx_fetch_all("select 
      posts.id,
      posts.title,
      categories.name as category_name,
      posts.created,
      posts.status,
      users.nickname as user_name
      from posts
      inner join categories on posts.category_id = categories.id
      inner join users on posts.user_id = users.id
      where {$where}
      order by posts.created desc
      limit {$skip}, {$page_size};");
  // 只显示可见5页
  $visiable = 5;
  $region = (int)($visiable-1)/2;
  // 开始和结束
  $begin = $page - $region;
  $end = $begin  + ($visiable-1);
  // 考虑合理性问题
   // 确保begin不会小于1 
  $begin = $begin< 1? 1: $begin;
  $end = $begin + ($visiable-1);
  // 确保end不会超出最大值
  $end = $end > $total_pages? $total_pages: $end; 
  // 因为end发生了变化 需要确保end和begin之间的关系
  $begin = $end - ($visiable-1); 
  // 确保begin不小于1 
  $begin = $begin<1? 1: $begin;
  
  // 查询出所有的分类
  $categories = bx_fetch_all('select * from categories;');

  // 处理数据格式转换 转换状态显示
  function convert_status($status){
      $dict = array (
          'published' => '已发布',
          'drafted'   => '草稿',
          'trashed'   => '回收站'
      );
      return isset($dict[$status])? $dict[$status]: '未知';
  }

  function convert_date($created) {
    // => '2017-10-23 10:23:34'
    $timestamp = strtotime($created);
    return date('Y年m月d日<b\r>H:i:s', $timestamp);
  }

  function get_category($category_id){
     return bx_fetch_one("select name from categories where id = '{$category_id}'")['name'];
  }

  function get_user($user_id){
     return bx_fetch_one("select nickname from users where id = '{$user_id}'")['nickname'];
  }

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>
  <div class="main">
    <?php include 'inc/navbar.php';?>
    <div class="container-fluid">
      <div class="page-title">
        <h1>所有文章</h1>
        <a href="post-add.php" class="btn btn-primary btn-xs">写文章</a>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <a class="btn btn-danger btn-sm" href="javascript:;" style="display: none">批量删除</a>
        <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF'];?>">
          <select name="category" class="form-control input-sm">
            <option value="all">所有分类</option>
            <?php foreach ($categories as $item): ?>
               <option <?php echo   isset($_GET['category']) && $item['id']==$_GET['category']? 'selected': '' ;?> value=<?php echo $item['id'];?>><?php echo $item['name']; ?></option>
            <?php endforeach ?>
          </select>
          <select name="status" class="form-control input-sm">
            <option value="all">所有状态</option>
            <option value="drafted"   <?php echo isset($_GET['status'])&& $_GET['status']==='drafted'? 'selected': '' ;?>>
                草稿
            </option>
            <option value="published" <?php echo isset($_GET['status'])&& $_GET['status']==='published'? 'selected': '' ;?>>
                已发布
            </option>
            <option value="trashed"   <?php echo isset($_GET['status'])&& $_GET['status']==='trashed'? 'selected': '' ;?>>
                回收站
            </option>
          </select>
          <button class="btn btn-default btn-sm">筛选</button>
        </form>
        <ul class="pagination pagination-sm pull-right">
          <li><a href="#">上一页</a></li>
          <?php for ($index=$begin; $index<=$end; $index++): ?>
                <li <?php echo $page==$index? ' class="active" ': '' ;?>>
                    <a href="?page=<?php echo $index.$search;?>"><?php echo $index; ?></a>
                </li>
          <?php endfor ?>
          <li><a href="#">下一页</a></li>
        </ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>标题</th>
            <th>作者</th>
            <th>分类</th>
            <th class="text-center">发表时间</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $item): ?>
             <tr>
                <td class="text-center"><input type="checkbox"></td>
                <td><?php echo $item['title'];?></td>
                <td><?php echo $item['user_name'];?></td>
                <td><?php echo $item['category_name'];?></td>
                <td class="text-center"><?php echo $item['created'];?></td>
                <td class="text-center"><?php echo convert_status($item['status']);?></td>
                <td class="text-center">
                  <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>
                  <a href="/admin/posts-delete.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-xs">删除</a>
                </td>
          </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php $current_page = 'posts';?>
  <?php include 'inc/sidebar.php';?>
  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>