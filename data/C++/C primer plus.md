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

## 指针常量和常量指针

注意：
```C++
const int * p;//常量指针，指针指向常量
int const * p;//常量指针，同上

int * const p;//指针常量，指针类型的常量
```

## 继承访问权限变化

|基类（父类）| public   |protected  |private|
|---        |---       |---        |---|
|公有继承   |public	    |protected	|不可见（不能访问）|
|保护继承	|protected	|protected	|不可见（不能访问）|
|私有继承	|private	|private	|不可见（不能访问）|
不管何种继承，基类的私有成员不能访问。
除私有成员，公有继承不改变基类访问权限。而保护继承、私有继承则是把访问权限变为对应继承的访问权限。

# 继承与面向对象设计

## 常见class之间的关系

### is-a

当子类公有继承基类，子类就可认为是一种基类。
```C++
class Drive:public Base{}
```

### has-a

### is-implemented-in-terms-of

1. 当子类私有继承基类，基类只能作为private共子类访问，不能被外部访问。也就是说基类在实现子类提供了帮助，但不会影响子类的外部行为。

2. 复合(composition)

私有继承比复合的复杂度低，但是复合更容易理解，复合可以降低编译依存性，同时私有继承都可以转为复合的形式，如下：
```C++
class MyClass:private Base{
    ...
}
//可以转为复合形式
class MyClass{
    private:
        class MyClassImple:public Base{...}
        //MyClassImple可以重新定义Base纯虚函数或者虚函数
    ...
}
```
复合还有个作用，可以阻止子类重新定义virtual函数。例如，Java可以用final修饰virtual，使得子类可能修改virtual函数，C++没有相关关键字，但可以使用复合。如下：
```C++
class Base{
    virtual fun();
}
class MyClass{
    class MyClassImple:public Base{}
    MyClass(){
        MyClassImple myClassImple;
    }
    //fun行为跟Base中的fun一致，但又失去了virtual特性
    fun(){
        return myClassImple.fun();
    }
}
```
建议：无论如何，只要可以，还是应该选择复合。