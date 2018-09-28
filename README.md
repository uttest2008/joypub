一个融合PPk开放协议和多链（比原链+以太坊）实现的去中心化社交网络群组应用原型，是网络论坛和移动微信群的结合体，通过比原链的交易特性来注册用户和发布社交群组信息，可以选择比原链或以太坊等不同公链来发送消息，还可以进一步结合比原智能合约实现红包、表情包等数字资产收益，在此过程中融合展现比原数字资产和PPk开放协议的结合特点,并兼容传统的网页浏览器来访问，体现融合区块链技术的对等万维网(WEB3.0)的原型概念。

可以通过下面两种方式访问：
1.传统的网址： http://btmdemo.ppkpub.org/joy/pub/
2.基于区块链的PPk ODIN标识网址： ppk:JOY/pub/

类似DAT、IPFS等正在发展中的WEB3.0开放协议，目前大众使用的电脑和手机浏览器还不能原生支持访问。要访问“ppk:joy/pub/”这样的ODIN标识网址，现在可以运行我们PPk开发的JAVA开源工具的代理服务，就能使用现有浏览器来访问PPK网络资源了，比如 http://btmdemo.ppkpub.org:8088/ 就是我们运行的示例服务，在浏览器里打开该代理服务网址然后输入要访问的 PPk ODIN标识网址就可以看到了。

主要特性：
1.发挥比原链侧重数字资产的技术特性，支持每个社群自主、有特色的数字资产发行和衍生功能收益，并通过比原链将来发展完善的去中心化数字资产交易市场很方便地进行流通。
2.通过PPk开放协议支持多链结合支持应用需求，所有应用数据也都通过ODIN标识支持跨链开放访问。
3.相比传统的网络论坛和移动微信群，通过PPk PTTP协议将网页服务器改成了依托比原区块链平台运行，这样一旦部署运行，即使无人维护也能持续运行，不用担心社群服务中断了。
4.将传统的网站域名改为基于区块链技术的PPk ODIN标识，这样不用每年续费，也能保证社群服务入口的长期稳定可用。

源码是PHP+JS编写的，可以自行部署运行， 注意需编辑ppk_joyblock.inc.php文本文件，根据自己的比原钱包节点相应修改里面的节点API地址和账户等参数。
