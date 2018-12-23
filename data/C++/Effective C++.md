---
toc:
    depth_from: 1
    depth_to: 6
    ordered: false
html:
    embed_local_images: true
    embed_svg: true
    offline: false
    toc: true
print_background: false
export_on_save:
    html: true
---
# 让自己习惯C++

## 确定对象使用前已经初始化

1. 内置类型
    对于内置类型，赋值和初始化成本相同。建议使用前赋初始值。
2. 对于自定义类型
    C++规定，类中成员变量的初始化动作发生在进入构造函数本体之前。如果直接在构造函数中直接赋值，成员变量是经历过初始化和然后在构造函数中赋值两个操作。
    所以建议，在构造函数中使用成员初始化列表(member initialization list)。
    ```C++
    class MyObject
    {
        public:
            Object(Object object, int x)
                :object(object),
                x(x)
            {}
            Object()
                :object(),
                x(0)
            {}
        private:
            Object object;
            int x;
    }
    ```
    注意：如果有构造函数没有入参，记得把成员内置对象初始化。同时，成员初始化列表中变量顺序并不是初始化顺序，而是成员变量在类中定义的顺序决定。为了避免迷惑，建议和定义顺序保持一致。
3. non-local static对象
    C++对定义不同编译单元内的non-local static对象的初始化次序没有明确定义。而相反，对local static对象初始化时机有明确规定，就是对一次调用它的时候。
    注：编译单元是产生单一目标文件的一些源码，基本是它的单一源码文件和include的头文件。
    所以，使用包含local static对象的reference-returning函数来代替non-local static对象。
    ```C++
    Object& Object()
    {
        static Object object;
        return object;
    }
    ```
    但是，在多线程中因为这些reference-returning函数含有static对象，导致行为不确定。应该来说，只要是non-const static对象在多线程都有问题。？？？
    建议：在程序单线程启动阶段，手工调用所有的reference-returning函数，这样就可消除与初始化有关的竞速问题了。

# 构造/析构/赋值运算

## 请给基类声明virtual析构函数

### 问题

例如，factory函数一般返回基类指针，指针指向heap中的一片内存。但是最后使用完毕后delete对调用基类的析构函数。
但是C++指出，**当derived对象经由base指针delete掉，而该基类带有一个no-virtual析构函数，其行为未定义。一般是调用基类的析构函数，销毁掉基类部分。这样就导致内存泄露的问题。**

### 方法

请给为了**多态用途**的基类声明virtual析构函数。

一般的，一个class带有virtual函数，表示它被当作base class。所以，任何class只要带有virtual函数几乎应该有一个virtual析构函数。

**注意：有时想把一个class声明为抽象类，但手头上没有pure virtual函数，可以把析构函数设为pure virtual。因为抽象类一般是多态用途的base类，而base类几乎都建议析构是virtual的**

并非所有base类都是多态用途，例如uncopyable类，这些基类就用需要virtual析构函数。

### 原理

如果析构函数不是virtual，则调用的函数在编译时已经确定，由于指针是基类指针，所以调用的就是基类的析构函数；
如果析构函数是virtual，则调用的函数在运行期间确定，对象会有一个虚表指针，指向一个虚表数组，元素是函数指针，指向对应的子类virtual函数，所以调用的是子类的析构函数。

## Uncopyable类

### 问题

### 方法

* 方法一
    把拷贝构造函数和赋值操作符声明为私有的。
    优点是实现简单，但是类的成员函数和友元函数可以使用私有函数，解决方法是拷贝构造函数和赋值操作符只声明不定义，这样在链接期会报错。
    ```C++
    class MyClass{
        private:
            MyClass(const MyClass&);            //只声明，不定义
            MyClass& operator=(const MyClass&); //只声明，不定义
        ...
    }
    ```
* 方法二
    继承禁止拷贝类Uncopyable。这样如果使用MyClass类的拷贝构造函数或者赋值操作符，会调用基类对应的函数，由于基类是私有函数，则编译器会报错。
    优点是把链接期报错问题提前到编译期。
    ```C++
    class Uncopyable{
        protected:
            Uncopyable(){}
            ~Uncopyable(){}
        private:
            Uncopyable(const Uncopyable&);
            Uncopyable& operator=(const Uncopyable&);
    }
    class MyClass:public Uncopyable{}
    ```
    **或者你可以使用Boost提供的版本，叫做noncopyable的class。**

# 资源管理

## RAII类管理资源(13 14)

为防止资源泄露，请使用RAII(Resource Acquisition Is Initialization，取得资源就初始化，被销毁就释放资源)对象。

### 智能指针

两个常被使用的RAII类是tr1::shared_ptr(regerence-counting smart pointer引用计数型智能指针)和auto_ptr(智能指针)。

* auto_ptr，是个类指针对象，也就是所谓的智能指针，含义是当指针销毁，会自动delete所指对象。注意,拷贝时会把对象地址传给拷贝者，而原有指针为null。
    ```C++
    void f()
    {
        std::auto_ptr<Resource> pRsc1(resourceFactory());
        /*使用pRsc资源*/
        std::auto_ptr<Resource> pRsc2(pRsc1);//现在pRsc2指向资源，而pRsc1为null
        pRsc1 = pRsc2;//现在pRsc1指向资源，而pRsc2为null
        /*运行到最后销毁，会调用Resource析构函数释放资源*/
    }
    ```
* tr1::shared_ptr，是regerence-counting smart pointer引用计数型智能指针。与auto_ptr不同的是，可以拷贝，只有当所有指针都销毁时，delete所指对象。**通常share_ptr是RAII类的最佳选择**

    ```C++
    void f()
    {
        std::tr1::shared_ptr<Resource> pRsc1(resourceFactory());
        /*使用pRsc资源*/
        std::auto_ptr<Resource> pRsc2(pRsc1);//现在pRsc1、pRsc2都指向资源
        pRsc1 = pRsc2;//同上，无任何改变
        /*运行到最后，当pRsc1、pRsc2都销毁，会调用Resource析构函数释放资源*/
    }
    ```
    当然，如果释放资源不是简单的delete内存，还需要做其他事情，比如文件关闭，数据库关闭，这时智能指针shared_ptr可以指定某一函数为删除器。形式是：`std::tr1::shared_ptr<Resource> pRsc1(resourceFactory(), deleter)`其中deleter是某一函数指针，当没有智能指针指向该资源，会调用deleter函数。**而auto_ptr则没有这个设定**。


注意：这两个智能指针都是delete对象，当对象是个对象数组时，则不会调用delete[]，则可以使用boost::scoped_array和boost::shared_array类来代替。

### 自定义RAII类

自定义的好处是可以定义想要的行为。

首先看下RAII类：
```C++
class ResourceManage
{
    public:
        explicit ResourceManage()
        {
            pRsc = resourceFactory();
        }
        ~ResourceManage()
        {
            resouceDestroy(pRsc);
        }
    private:
        Resource *pRsc;
}
```
自定义行为：
1. 禁止复制
    方法是，使用继承禁止拷贝类来实现`class ResourceManage:private Uncopyable{...}`
2. 对底层资源使用引用计数
    方法是，RAII内部的私有资源指针换成share_ptr来实现
3. 转移底部资源的拥有权
    方法是，把RAII内部的私有资源指针换成auto_ptr来实现
4. 复制底部资源
    方法是，重载拷贝构造函数和赋值运算符

## 通过RAII类使用资源

RAII类并不是封装资源，而是为了确保资源释放一定会发生。但是由于把资源或资源指针作为私有成员，这个类也阻碍我们对资源的使用。例如：

```C++
class Resource
{
    ...
    doSomeThing(){...}//资源类有个api会对资源做些事情
    ...
}

void f()
{
    ResourceManage rscMag;//资源获取
    rscMag->doSomeThing()//错误!!!doSomeThing不是ResourceManage的成员方法
}
```
方法：
1. 显示转换
    显示的提供get函数，把内部资源或资源指针返回。
    注意，由于ResourceManage类不是为了封装资源，所以通过公有函数把私有成员返回也很正常。这样不仅隐藏客户不需要看到的部分，但同时也为客户全面准备好所有东西。
    ```C++
    rscMag.get()->doSomeThing()
    ```
    相似地，智能指针tr1::shared_ptr和auto_ptr也有get成员函数，把自己转为普通指针。
2. 隐式转换
    RAII类，改良版
    ```C++
    class ResourceManage
    {
        public:
            explicit ResourceManage()
            {
                pRsc = resourceFactory();
            }
            ~ResourceManage()
            {
                resouceDestroy(pRsc);
            }
            operator Resource() const{return pRsc;}//定义隐式转换Resource类型函数
        private:
            Resource *pRsc;
    }
    ```
注意：
    虽然隐式转换更符合书写，但是毕竟是隐私转换，可能增加错误转换的风险。所以一般显式转换比较安全，隐式转换比较方便。
    
## 以独立语句将newed对象置入智能指针

用RAII类管理资源，或多或少的使用智能指针。而资源对象初始化后赋值给智能指针，需要单独一句。如下：
```C++
//这里资源初始化和使用放到一条语句中。
//C++并没有定义同一条语句，逻辑上没有关联的步骤的执行先后顺序。
//如果在new Resource执行后执行getPara()异常终端，资源指针还没有传给智能指针。这样会导致后面资源始终没有释放。
doSomeThing(std::tr1::shared_ptr<Resource>(getResource()), getPara());
```
应该这样做:
```C++
std::tr1::shared_ptr<Resource> pRsc(getResource());//像这些关键步骤最后单独一个语句
doSomeThing(pRsc, getPara());
```
注意：其中getResource是factory函数，其返回Resource的指针。为了防止用户没有及时把指针传给智能指针，可以把factory函数设计成返回智能指针。

# 设计与声明

## 让接口容易被正确使用，不易被误用

比如一个接口是设置日期，但是参数容易误用。
```C++
void setDate(int month, int day, int year);//setDate的声明

setDate(30, 3, 1999);//错误，应该是3, 30, 1999的
setDate(3, 32,1995);//错误，3月没有32号
```
一种方法是把入参class化或者struct化。
```C++
void setDate(const Month& m, const Day& d, const Year& y);//setDate的声明
struct Day{
    explicit Day(int d):val(d){}
    int val;
}
...
setDate(Day(30), Month(3), Year(1995));
```
但有时候，参数表示的意义不能用基本类型来表示。比如获取日期的其中一个字段。
```C++
int getNumByField(String field);

int year = getNumByField("yeer");//错误，应该是year的
```
大部分人会想到使用宏或者枚举，但是这些都是基本类型，还是容易犯错。可以使用如下：
```C++
int getNumByField(const DateField& dateField);
class DateField{
    public:
        static DateField year(){return DateField("year");}
    ...
    private:
        explicit Month(String field):field(field){};
        String field;
}
getNumByField(DateField.year());
```

## 用pass-by-reference 替换 pass-by-value

### 问题

```c++
//函数声明
void doSomeThing(Base b);
class Base{};
class Drive:public Base{};
//函数使用
void doSomeThing(Drive d);
```
其中Drive类为实参，而Base类为形参。参数传递时会调用Base类的拷贝构造函数，进而会把Drive的特性丢失掉，造成“切割问题”。

### 方法

用pass-by-reference 替换 pass-by-value。其实C++编译器底层就是用指针实现引用的。改为指针也能解决，但会引入指针相关的问题。

注意：内置类型大部分比指针简单，所以内置类型还是推荐使用pass-by-value