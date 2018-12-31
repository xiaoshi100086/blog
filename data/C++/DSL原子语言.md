由于计算机的程序模型较为单一（归根结底都是运算和存储）。我们必须不断地将业务领域中的概念转换成相应的代码模型，然后再进行修改。这种间接性直接造成了软件的复杂度。

DSL首要的目的，是使程序尽可能地接近业务领域中的问题，从而消除不必要的间接性和复杂性。

Builder模式

处理步骤
1、DSL脚本；
2、解析脚本；
3、语义模型；
4、生成代码或者执行模型。

DSL分类
内部DSL和外部DSL

内部DSL指的是通过某种通用的编程语言（称为宿主语言）的语法编写出来的DSL，该DSL语法受制于宿主语言的语法，但不需要额外的规则去解析。
例如：
factory()
    ->obj()
        ->attr()
        ->attr()
    ->if()
        ->condition()
        ->command()
    ->endif()
->end();
利用c++的对象来实现dsl语言。其中由工厂函数factory()生成对象，然后调用对象的成员函数，注意成员函数必须返回this指针以便继续调用其他成员函数。

外部DSL是自己实现的新语言，不受任何限制，但工作量大（毕竟是要开发一种新语言），需要文本解析，构造抽象语法树。XML可以认为是一种外部DSL，虽然容易解析，但XML的结构制作太多冗余的噪声，影响可读性，而且不适合描述一下流程结构，如if，else，loop等
例如，xml文件：
<factory>
    <obj>
        <attr/>
        <attr/>
    </obj>
    <if>
        <condition/>
        <command/>
    </if>
</factory>
或者，自然语言：
install obj madeof [attr, attr]
    if comdition command endif
