# build_machine_vbox
Docker-machineとVirtualBoxを用いてLAMP開発環境を構築するプロジェクトです。
サンプルとして簡易なチャットアプリを起動できます

# 最初に
・あなたが所持しているWindowsマシーンやMacマシーンをホストとします
・Docker-machineで構築した仮想マシーンをDockerホストとします
・Dockerで構築したものをDockerコンテナとします

# 構築方法
０１． 当サンプルプロジェクトをクローンしてください
０２． https://docs.docker.com/toolbox/overview/ から Docker Toolbox をインストールする
０３． Docker Quickstart Terminal を起動
０４． 立ち上がったら $docker-machine ip で仮想マシーンのIPアドレスを確認する
０５． IPアドレスが [192.168.99.100] 以外の場合、クローンしたプロジェクト配下の [project/app_server/test_app/js/config.js] [project/app_server/test_app/config.php] の [192.168.99.100] を ０４． で確認したものへ変更
０６． VirtualBox を起動
０７． default という仮想マシーンが立ち上がっているので選択
０８． default の共有フォルダを設定する（フォルダーのパスはクローンしたもの、フォルダ名は [/home/docker/任意の名前] ）
０９． Docker Quickstart Terminal から $docker-machine restart で仮想マシーンの再起動
１０． 再起動が完了したら $docker-machine ssh で仮想マシーンに接続
１１． [compose-install.sh] [docker-compose.yml] がある project フォルダまで移動
１２． $docker-compose -v で docker-compose がインストールされているか確認
１３．　インストールされていなかったら $sudo ./compose-install.sh で docker-compose をインストール
１４．　$docker-compose up -d
１５．　ブラウザで [http://192.168.99.100/test_app/] または ０５． で変更した場合は [http://~変更したIP~/test_app/] を確認する
１６． テストアプリが起動していたら完了です！

#　立ち上がった環境で開発
・共有フォルダで指定したホスト側のコードを編集すると、Dockerホスト→Dockerコンテナに反映されますので、従来の感覚で開発ができます。
・PHPMyAdminを起動するには ０４． で確認したIPに [:8000] のポート番号をつけてアクセスしてください。[User=root:Pass=00000]
・$docker-compose stop でDockerコンテナ停止
・$docker-compose start でDockerコンテナ起動
・$docker-compose rm　でDockerコンテナの削除