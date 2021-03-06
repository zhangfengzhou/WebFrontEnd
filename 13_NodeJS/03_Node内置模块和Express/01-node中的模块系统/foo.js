var foo = 'bar';

function add(x, y){
   return x+y;
}

// 只能得到我想给你的成员
// 这样做的目的为了解决变量命名冲突的问价
// exports.add = add;

// 如果一个模块需要直接导出某个成员，而非挂在全部
// 那这个时候必须使用下面这种方式
module.exports = 'hello'

// exports 是一个对象
// 我们可以通过多次为这个对象添加成员实现

exports.str = 'hello';

// 现在我有个需求
// 我希望加载得到直接就是一个：
// 方法
// 字符串
// 