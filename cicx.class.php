<?php
require_once('access.class.php');
error_reporting(0);
class CICX extends Access {

    # Create the PDO database object
    public function __construct() {
        try {
            $this->db = new PDO('mysql:dbname=' . self::DB_NAME . ';host=' . self::DB_HOST, self::DB_USER, self::DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        } catch (PDOException $e) {
            file_put_contents('PDOErrors.txt', date('d-m-Y H:i:s') . ' - ' . $e->getMessage() . "\r\n", FILE_APPEND);
        }
    }





    # Adds an order from redistribute deliveries
    public function addDelivery(){

        $orders = explode(',', $_GET['order']);
        foreach($orders as $order):
            $data = array(
                ':id_order' => $order,
                ':id_sender' => $_GET['sender']
                );
            $this->db
                ->prepare("INSERT INTO deliveries (id_sender, id_order)
                        VALUES (:id_sender, :id_order)")
                ->execute($data);

            $this->db
                ->prepare("UPDATE orders SET has_sender = 1 WHERE id = $order")
                ->execute();
        endforeach;
        die();
    }





    # Record a new order
    public function addOrder(){

        # Saves the data from the order
        $is_paid = (isset($_POST['is_paid']) == 'on') ? 1 : 0;
        $no_delivery = (isset($_POST['no_delivery']) == 'on') ? 1 : 0;
        $card = (isset($_POST['card']) == 'on') ? 1 : 0;

        $data = array(
            ':created_at' => date('Y-m-d H:i:s'),
            ':id_seller' => $_SESSION['id'],
            ':id_buyer' => $_POST['id_buyer'],
            ':id_receiver' => $_POST['id_gifted'],
            ':id_product' => $_POST['id_product'],
            ':card' => $card,
            ':card_from' => $_POST['card_from'],
            ':card_to' => $_POST['card_to'],
            ':is_paid' => $is_paid,
            ':no_delivery' => $no_delivery,
            ':notes' => $_POST['notes'],
            ':delivery_at' => $_POST['delivery_at']
            );

        @$this->db
            ->prepare("INSERT INTO orders (created_at, id_seller, id_buyer, id_receiver, id_product, card, card_from, card_to, is_paid, no_delivery, notes, delivery_at)
                VALUES (:created_at, :id_seller, :id_buyer, :id_receiver, :id_product, :card, :card_from, :card_to, :is_paid, :no_delivery, :notes, :delivery_at)")
            ->execute($data);


        # Verifies if the supply is over
        $data = array(':id_product' => $_POST['id_product']);
        $query = $this->db
            ->prepare("SELECT count(1) as items_sold, (SELECT supply FROM products WHERE products.id = :id_product) as items_total, (SELECT name FROM products WHERE products.id = :id_product) as product_name FROM orders WHERE orders.id_product = :id_product");
            $query->execute($data);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            $supply = $row['items_total'] - $row['items_sold'];
            $product_name = $row['product_name'];

            # If there's no supply anymore, sends an email to the admins
            if($supply == 0):
                $query = $this->db
                    ->query("SELECT email FROM users WHERE is_admin = 1");
                $admins_email = $query->fetchAll(PDO::FETCH_ASSOC);
                foreach($admins_email as $email):
                    mail($email['email'],'Notificação de estoque',"O estoque para o produto '$product_name' acabou.");
                endforeach;

                $data = array(':id_product' => $_POST['id_product']);
                @$this->db
                    ->prepare("UPDATE products SET out_of_stock = 1 WHERE id = :id_product")
                    ->execute($data);
            endif;

            header('location: index.php?success=1');
    }





    # Adds a new product
    public function addProduct(){
        $data = array(
            ':name' => $_POST['name'],
            ':price' => $_POST['price'],
            ':supply' => $_POST['supply']
            );

        @$this->db
            ->prepare("INSERT INTO products (name, price, supply)
                VALUES (:name, :price, :supply)")
            ->execute($data);

        header('location: index.php?success=1');
    }





    # Creates a new user
    public function addKeySender() {
        $data = array(
            ':id' => $_POST['id'],
            ':name' => $_POST['name']
            );
        $query = $this->db
            ->query("SELECT id FROM users WHERE name = '{$_POST['name']}'");
        $id_delivery = $query->fetchColumn();
        $query = $this->db
            ->query("SELECT id FROM key_senders WHERE id = {$_POST['id']} AND id_delivery = $id_delivery");
        if(!$query->fetchColumn()):
            @$this->db
                ->prepare("INSERT IGNORE INTO key_senders (id, id_delivery)
                    VALUES ({$_POST['id']}, $id_delivery)")
                ->execute($data);
        endif;
        header('location: index.php?keySender=' . $_POST['id']);
    }





    # Creates a new user
    public function addUser(){
        $is_gifted_order = (isset($_POST['is_order']) && $_POST['is_order'] == 'yes' && isset($_POST['is_gifted']) && $_POST['is_gifted'] == 'on') ? true : false;

        $name = ($is_gifted_order) ? $_POST['gifted-name'] : $_POST['name'];

        # Look up if user already exists
        $data = array(':name' => $name);
        $query = $this->db
            ->prepare("SELECT id
                FROM users
                WHERE name = :name");
        $query->execute($data);

            if($row = $query->fetch(PDO::FETCH_ASSOC)): ?>
                <html lang="pt-BR">
                <head>
                    <meta charset="utf-8">
                    <title>Centro Infantil Chico Xavier</title>
                </head>
                <script type="text/javascript">
                    alert("Esse usuário já existe.\nTente novamente.");
                    window.history.back();
                </script>
                </html>
            <?php
                die();
            endif;

        $is_seller = (isset($_POST['is_seller']) == 'on') ? 1 : 0;
        $is_buyer = (isset($_POST['is_buyer']) == 'on') ? 1 : 0;
        $is_sender = (isset($_POST['is_sender']) == 'on') ? 1 : 0;
        $is_gifted = (isset($_POST['is_gifted']) == 'on') ? 1 : 0;
        $password = !empty($_POST['password']) ? $password = md5($_POST['password']) : '';
        $data = array(
            ':created_at' => date('Y-m-d H:i:s'),
            ':name' => $_POST['name'],
            ':email' => $_POST['email'],
            ':password' => $password,
            ':phone' => $_POST['phone'],
            ':mobile' => $_POST['mobile'],
            ':address' => $_POST['address'],
            ':address2' => $_POST['address2'],
            ':reference' => $_POST['reference'],
            ':district' => $_POST['district'],
            ':city' => $_POST['city'],
            ':province' => $_POST['province'],
            ':geolat' => $_POST['geolat'],
            ':geolng' => $_POST['geolng'],
            ':notes' => $_POST['notes'],
            ':is_seller' => $is_seller,
            ':is_buyer' => $is_buyer,
            ':is_sender' => $is_sender,
            ':is_gifted' => $is_gifted
            );

        if($is_gifted_order):
            $data = array(
                ':created_at' => date('Y-m-d H:i:s'),
                ':name' => $_POST['gifted-name'],
                ':email' => $_POST['gifted-email'],
                ':password' => '',
                ':phone' => $_POST['gifted-phone'],
                ':mobile' => $_POST['gifted-mobile'],
                ':address' => $_POST['gifted-address'],
                ':address2' => $_POST['gifted-address2'],
                ':reference' => $_POST['gifted-reference'],
                ':district' => $_POST['gifted-district'],
                ':city' => $_POST['gifted-city'],
                ':province' => $_POST['gifted-province'],
                ':geolat' => $_POST['gifted-geolat'],
                ':geolng' => $_POST['gifted-geolng'],
                ':notes' => $_POST['gifted-notes'],
                ':is_seller' => $is_seller,
                ':is_buyer' => $is_buyer,
                ':is_sender' => $is_sender,
                ':is_gifted' => $is_gifted
            );
        endif;

        @$this->db
            ->prepare("INSERT INTO users (created_at, name, email, password, phone, mobile, address, address2, reference, district, city, province, geolat, geolng, notes, is_seller, is_buyer, is_sender, is_gifted)
                VALUES (:created_at, :name, :email, :password, :phone, :mobile, :address, :address2, :reference, :district, :city, :province, :geolat, :geolng, :notes, :is_seller, :is_buyer, :is_sender, :is_gifted)")
            ->execute($data);
        $id_sender = $this->db->lastInsertId();


        # Adds also the favorite districts of the sender, if selected
        if($is_sender == 1):
            if(!empty($_POST['district1']) || !empty($_POST['district2']) || !empty($_POST['district3']) || !empty($_POST['district4']) || !empty($_POST['district5'])):
                $data = array(
                    ':id' => $id_sender,
                    ':district1' => $_POST['district1'],
                    ':district2' => $_POST['district2'],
                    ':district3' => $_POST['district3'],
                    ':district4' => $_POST['district4'],
                    ':district5' => $_POST['district5']
                    );

                @$this->db
                    ->prepare("INSERT INTO senders (id, district1, district2, district3, district4, district5)
                        VALUES (:id, :district1, :district2, :district3, :district4, :district5)")
                    ->execute($data);
            endif;
        endif;

        # When adding an order, returns with the name field filled in
        if(isset($_GET['return'])):
            $buyer_name = ($is_buyer == 1) ? $_POST['name'] : $_POST['remember-buyer-name'];
            $gifted_name = ($is_gifted == 1) ? $_POST['gifted-name'] : $_POST['remember-gifted-name'];
            header('location: index.php?formAddOrder&buyer=' . $buyer_name . '&gifted=' . $gifted_name);
        else:
            header('location: index.php?success=1');
        endif;
    }






    # Shows a report of the hour when senders left and came back from the deliveries
    public function adminSenders(){
        $order_by = ($_GET['order']) ? "delivery_{$_GET['order']} {$_GET['by']}, " : '';
        $query = $this->db
            ->query("SELECT senders.id, senders.delivery_leaving, senders.delivery_coming, users.name, users.phone, users.mobile, start_time, date_beginning, date_finish
            FROM senders
            RIGHT JOIN users ON senders.id = users.id
            WHERE users.is_admin IS NULL AND users.is_sender = 1 AND users.is_active = 1
            ORDER BY $order_by users.name");
        $rs = $query->fetchAll(PDO::FETCH_ASSOC);
        $count_array_senders = count($rs);
        if(!empty($rs)): ?>
        <div id="list">
        <h1>Acompanhamento de saída de entregadores (<?php echo $count_array_senders; ?>)</h1>
        <ul>
            <li class="sticky">
                <label class="name">Usuário</label>
                <label class="phone">Contato</label>
                <label class="time"><a href="index.php?adminSenders&order=leaving&by=<?php echo ($_GET['order'] === 'leaving' && $_GET['by'] === 'desc') ? 'asc' : 'desc'; ?>">Horário de saída</a></label>
                <label class="time"><a href="index.php?adminSenders&order=coming&by=<?php echo ($_GET['order'] === 'coming' && $_GET['by'] === 'desc') ? 'asc' : 'desc'; ?>">Horário de retorno</a></label>
<!-- 				<label class="notes">Gerenciar horário</label> -->
            </li>
        <?php
            $now = date('H:i:s');

            foreach($rs as $row): ?>
            <li>
                <label class="name"><?php echo $row['name']; ?></label>
                <label class="phone"><?php echo $row['mobile']; ?><?php if(!empty($row['phone']) && !empty($row['mobile'])) echo ' / '; ?><?php echo $row['phone']; ?></label>

                <label class="time">
                    <input class="delivery-leaving" <?php if($row['delivery_leaving']) echo 'checked="checked"'; ?> data-id="<?php echo $row['id']; ?>" type="checkbox">
                    <!--
                    <a href="javascript:;" class="trackDelivery" data-time="<?php echo substr($row['date_beginning'],11,5); ?>" data-id="<?php echo $row['id']; ?>" data-name-type="saída" data-type="beginning"><?php
                    if($row['date_beginning'] == '0000-00-00 00:00:00'):
                        echo 'Editar';
                    else:
                        echo substr($row['date_beginning'],11,5);
                    endif;
                    ?></a>
-->
                </label>
                <label class="time">
                    <input class="delivery-coming" <?php if($row['delivery_coming']) echo 'checked="checked"'; ?> data-id="<?php echo $row['id']; ?>" type="checkbox">
                    <!--
<a href="javascript:;" class="trackDelivery" data-time="<?php echo substr($row['date_finish'],11,5); ?>" data-id="<?php echo $row['id']; ?>" data-name-type="retorno" data-type="finish"><?php
                    if($row['date_finish'] == '0000-00-00 00:00:00'):
                        echo 'Editar';
                    else:
                        echo substr($row['date_finish'],11,5);
                    endif;
                    ?></a>
-->
                </label>
                <label class="notes">
<!-- 					<a href="index.php?resetTime=<?php echo $row['id']; ?>">Zerar</a> -->
                </label>
            </li>
            <?php endforeach; ?>
        </ul>
        </div>
        <?php
        else: ?>
        <div id="list">
            <h3>Nenhum entregador com entregas cadastrado ainda.</h3>
        </div>
        <?php endif;
    }






    # Shows all orders
    public function allDeliveries(){
    ?>
    <div id="list">
        <h1><img src="img/deliveries.png" alt="Listando todas entregas" title="Listando todas entregas" /> Listando todas entregas <?php if($_GET['allDeliveries'] == 'problems') echo 'com problemas';?></h1>
    <?php if($_GET['allDeliveries'] == 'problems'): ?>
        <a href="index.php?problemsReport" class="button" target="_blank">Gerar relatório das entregas com problemas</a>
    <?php endif; ?>
    <?php if($_SESSION['type'] == 'admin' && $_GET['allDeliveries'] != 'problems'): ?>
        <a href="index.php?closestDeliveries" class="button finish">Distribuir entregas</a>
        <a href="index.php?tags" class="button" target="_blank">Gerar etiquetas</a>
        <a href="index.php?sendersTags" class="button" target="_blank">Gerar etiquetas dos entregadores</a>
        <br />
        <a href="index.php?allDeliveries=problems" class="button">Ver entregas com problemas</a>
        <a href="index.php?listKeySenders" class="button">Ver entregadores-chave</a>
        <a href="index.php?adminSenders" class="button">Saída dos entregadores</a>
    <?php endif; ?>
        <?php
        if($_GET['allDeliveries'] == 'problems'):
            $select = ''; $inner_join = ''; $order_by = '';
            $select = ", problems.id as 'problem_id', problems.problem, problems.note, problems.is_resolved";
            $inner_join = "INNER JOIN problems ON problems.id_order = orders.id";
            $order_by = "problems.is_resolved ASC, ";
            $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, orders.delivery_at, orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to,
                u1.name as 'seller_name', u1.phone as 'seller_phone', u1.mobile as 'seller_mobile',
                u2.name as 'buyer_name', u2.phone as 'buyer_phone', u2.mobile as 'buyer_mobile',
                u3.id as 'gifted_id', u3.name as 'gifted_name', u3.phone, u3.mobile, u3.district as 'gifted_district', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province,
                p1.name as 'product_name' $select
                FROM orders
                INNER JOIN users as u1 ON orders.id_seller = u1.id
                INNER JOIN users as u2 ON orders.id_buyer = u2.id
                INNER JOIN users as u3 ON orders.id_receiver = u3.id
                INNER JOIN products as p1 ON orders.id_product = p1.id
                $inner_join
                ORDER BY $order_by orders.created_at DESC");

            $deliveries = $query->fetchAll(PDO::FETCH_ASSOC);

            if($_GET['allDeliveries'] == 'problems' && empty($deliveries)): ?>
                <h2>Nenhum problema</h2>
            <?php else: ?>
            <ul>
                <li class="sticky">
                    <label class="id">Id</label>
                    <label class="name">Entregador</label>
                    <label class="name">Vendedor</label>
                    <label class="name">Comprador</label>
                    <label class="full-address">Presenteado</label>
                    <label class="card">Cartão</label>
                    <label class="full-data">Notas</label>
                </li>

                <?php foreach($deliveries as $row): ?>

                    <li id="deliveries-<?php echo $row['id']; ?>">
                        <label class="id"><?php echo $row['id']; ?></label>
                        <?php
                            $query = $this->db->query("SELECT users.id, users.name, users.phone, users.mobile
                                FROM deliveries
                                JOIN users ON deliveries.id_sender = users.id
                                WHERE id_order = {$row['id']}");
                            $sender_data = $query->fetchAll();
                        ?>
                        <label class="name tooltip"><?php echo $sender_data[0]['name']; ?>
                        <address><?php echo $sender_data[0]['mobile']; ?><?php if(!empty($sender_data[0]['phone']) && !empty($sender_data[0]['mobile'])) echo ' / '; ?><?php echo $sender_data[0]['phone']; ?></address>
                        </label>

                        <label class="name tooltip"><?php echo $row['seller_name']; ?>
                            <address><?php echo $row['seller_mobile']; ?><?php if(!empty($row['seller_phone']) && !empty($row['seller_mobile'])) echo ' / '; ?><?php echo $row['seller_phone']; ?></address>
                        </label>
                        <label class="name tooltip"><?php echo $row['buyer_name']; ?>
                            <address><?php echo $row['buyer_mobile']; ?><?php if(!empty($row['buyer_phone']) && !empty($row['buyer_mobile'])) echo ' / '; ?><?php echo $row['buyer_phone']; ?></address>
                        </label>
                        <label class="full-address">
                            <h4><?php echo $row['gifted_name']; ?></h4>
                            <p><?php echo $row['mobile']; ?><?php if(!empty($row['phone']) && !empty($row['mobile'])) echo ' / '; ?><?php echo $row['phone']; ?></p>
                            <p><?php echo $row['address']; ?>
                            <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                            <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                            <strong><?php echo $row['district']; ?></strong> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?></p>
                        </label>
                        <label class="card tooltip"><?php echo ($row['card']) ? 'Sim' : 'Não'; ?><?php if($row['card']): ?><address><?php echo "<strong>De</strong>: {$row['card_from']}<br /><strong>Para</strong>: {$row['card_from']}"; ?></address><?php endif; ?></label>
                        <label class="full-data">
                        <?php
                        if($row['is_paid']) echo '<p><img src="img/paid.png" alt="Pago" title="Pago" /> Pago</p>';
                        if($row['no_delivery']):
                            echo 'Sem entrega';
                        endif;

                        if(isset($row['problem'])): ?>
                            <p class="<?php echo ($row['is_resolved']) ? "success" : "error"; ?>"><?php echo $row['problem']; ?>
                            <em><?php echo $row['note']; ?></em></p>
                            <a class="noteProblem" href="javascript:;" data-id="<?php echo $row['problem_id']; ?>"><img src="img/update.png" alt=""> Editar nota</a>
                            <a class="problem" href="index.php?deleteProblem=<?php echo $row['problem_id']; ?>"><img src="img/remove.png" alt=""> Problema resolvido</a>
                        <?php endif; ?>
                            <a class="report" href="index.php?printDeliveries=<?php echo $sender_data[0]['id']; ?>" target="_blank"><img src="img/report.png" alt="" />Gerar ficha</a></p>
                        </label>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php else:

 endif; ?>
        </div>
<?php
    }





    # Changes the seller password
    public function boxChangePassword(){ ?>
        <div id="list">
            <h1><img src="img/icon-password-big.png" alt="Alterar senha" title="Alterar senha" /> Alterar senha</h1>

            <form action="index.php?changePassword" id="form-change-password" method="post">
                <input name="id" type="hidden" value="<?php echo $_GET['id']; ?>">
                <input id="old-password" name="old-password" placeholder="Senha antiga" type="password" required /><br />
                <input id="new-password" name="new-password" placeholder="Nova senha" type="password" required /><br />
                <input id="confirm-password" name="confirm-password" placeholder="Repetir nova senha" type="password" required /><br />
                <input type="submit" value="Alterar senha" />
            </form>
        </div>
        <?php
    }





    # Change user level to gifted
    public function buyerIsGifted(){
        $data = array(':buyer' => $_GET['buyer']);
        @$this->db
            ->prepare("UPDATE users
                SET is_gifted = 1
                WHERE name = :buyer")
            ->execute($data);
    }





    # Changes the seller password
    public function changePassword(){
        $data = array(
            ':id' => $_POST['id'],
            ':old-password' => $_POST['old-password']
        );
        $query = $this->db
            ->prepare("SELECT COUNT(1)
                FROM users
                WHERE id = {$_POST['id']} AND password = MD5('{$_POST['old-password']}')");
        $query->execute($data);
        $row = $query->fetchColumn();

            if($row == 1):
                $query = $this->db
                    ->prepare("UPDATE users
                        SET password = MD5('{$_POST['new-password']}')
                        WHERE id = {$_POST['id']}");
                if($query->execute($data)): ?>
                    <div id="list">
                        <h2><img src="img/icon-password-big.png" alt="Alterar senha" title="Alterar senha" /> Senha alterada.</h2>
                    </div>
                <?php else: ?>
                    <div id="list">
                        <h2><img src="img/icon-password-big.png" alt="Alterar senha" title="Alterar senha" /> Ocorreu um erro.</h2>
                        <a href="index.php?boxChangePassword">Tente novamente</a> ou entre em contato com o administrador.
                    </div>
                <?php endif; ?>

        <?php else: ?>
            <div id="list">
                <h2><img src="img/icon-password-big.png" alt="Alterar senha" title="Alterar senha" /> A senha antiga não confere.</h2>
                <a href="index.php?boxChangePassword">Tente novamente</a> ou entre em contato com o administrador.
            </div>
        <?php
        endif;
    }





    # Updates all sender to active/inactive
    public function check_all(){
        $check_all = $_GET['check_all'];
        $query = $this->db
            ->query("UPDATE users
                SET is_active = $check_all");
        die();
    }





    # Remove all unused data to start anew the next year
    public function cleanTables(){
        $this->db->query("TRUNCATE TABLE deliveries;");
        $this->db->query("TRUNCATE TABLE orders;");
        $this->db->query("TRUNCATE TABLE problems;");
        $this->db->query("TRUNCATE TABLE products;");
        $this->db->query("UPDATE users SET is_buyer = NULL;");
        $this->db->query("UPDATE users SET is_gifted = NULL;");
        $this->db->query("UPDATE users SET is_confirmed = NULL;");
        $this->db->query("UPDATE users SET is_active = 0 WHERE is_active = 1;");
        $this->db->query("UPDATE users SET is_gifted = 1 WHERE name = 'Sem entrega';");
        $this->db->query("DELETE FROM users WHERE is_sender = 0 AND is_seller = 0 AND is_admin IS NULL;");
        header('location: index.php?reset=1');
    }





    public function closestDeliveries(){
        # Creates a list with every sender id to distribute their deliveries
        $list_senders = array();
        $this->db->query("DELETE FROM deliveries");
        $this->db->query("UPDATE orders SET has_sender = NULL");


        # Distribute key senders first
        $query = $this->db
            ->query("SELECT ks.*, orders.id as id_order
                FROM key_senders as ks
                JOIN orders ON orders.id_receiver = ks.id_delivery
                JOIN users ON ks.id = users.id
                WHERE users.is_active = 1");
        $array_key_senders = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($array_key_senders as $key_sender):
            $data = array(':id_sender' => $key_sender['id'], ':id_order' => $key_sender['id_order']);
            $query = $this->db
                ->prepare("INSERT INTO deliveries
                    (id_sender, id_order) VALUES (:id_sender, :id_order)");
            $query->execute($data);
            $query = $this->db
                ->prepare("UPDATE orders
                    SET has_sender = true WHERE id = {$key_sender['id_order']}");
            $query->execute();
        endforeach;



        # Then distributes other users
        $query = $this->db
            ->query("SELECT users.id
                FROM users
                LEFT JOIN senders ON users.id = senders.id
                WHERE users.is_sender = 1 AND users.is_active = 1
                GROUP BY users.id
                HAVING id NOT IN (SELECT key_senders.id FROM key_senders WHERE id_delivery IS NULL)
                ORDER BY users.id ASC");
        $array_senders = $query->fetchAll(PDO::FETCH_ASSOC);
        $count_array_senders = count($array_senders);


        foreach($array_senders as $sender):
            $list_senders[] = $sender['id'];
        endforeach;


        # Calculates how many deliveries each sender should make
        $query = $this->db
            ->query("SELECT COUNT(DISTINCT(orders.id_receiver)) as count_orders
                FROM orders
                WHERE orders.no_delivery != 1 AND orders.has_sender IS NULL");
        $row_deliveries = $query->fetch(PDO::FETCH_ASSOC);
        $ceil_count_deliveries = ceil($row_deliveries['count_orders'] / $count_array_senders);
        ?>


            <div id="list">
                <h1><img src="img/deliveries.png" alt="Entregas distribuídas" title="Entregas distribuídas" />Entregas distribuídas</h1>
                <h3>Existem <?php echo $row_deliveries['count_orders']; ?> entregas* para <?php echo $count_array_senders; ?> entregadores. Cada entregador ficará responsável por até <?php echo $ceil_count_deliveries; ?> entregas.</h3>
                <small>* Esse valor considera o total de entregas, já que um mesmo presenteado pode receber várias cestas.</small>
            </div>

        <?php
        $modulus = $row_deliveries['count_orders'] % $count_array_senders;

        foreach($list_senders as $current_sender):
            $max_delivery_time = '';
            $max_delivery_diff = '';

            # Finds the maximum time acceptable for delivery
/*
            $query = $this->db
                ->query("SELECT ADDTIME(MIN(orders.delivery_at), '$max_delivery_diff')
                FROM orders
                INNER JOIN users ON orders.id_receiver = users.id
                WHERE users.geolat != 0 AND users.geolng != 0 AND orders.has_sender IS NULL AND orders.no_delivery != 1 AND orders.delivery_at != '00:00:00'");
            $max_delivery_time = $query->fetchColumn();
*/

            $count_deliveries = ($modulus > 0)
                ? $ceil_count_deliveries
                : floor($row_deliveries['count_orders'] / $count_array_senders);




            # Matches every preferred district from the sender with an existing order
            for($j=1; $j <= $count_deliveries; $j++):


                if($j < 6):
                    $query = $this->db
                        ->query("SELECT district, geolat, geolng
                            FROM senders
                            INNER JOIN districts ON districts.district = senders.district$j
                            WHERE senders.id = $current_sender");
                    $origin_district = $query->fetch(PDO::FETCH_ASSOC);
                else:
                    $query = $this->db
                        ->query("SELECT district, geolat, geolng
                            FROM users
                            WHERE id = $current_sender");
                    $origin_district = $query->fetch(PDO::FETCH_ASSOC);
                endif;



                if(!empty($origin_district['district'])):
                    $query = $this->db
                        ->query("SELECT DISTINCT(orders.id_receiver), orders.id, users.district, users.geolat, users.geolng, orders.delivery_at
                        FROM orders
                        INNER JOIN users ON orders.id_receiver = users.id
                        WHERE users.district = '{$origin_district['district']}' AND users.geolat != 0 AND users.geolng != 0 AND orders.has_sender IS NULL AND orders.no_delivery != 1 #AND orders.delivery_at != '00:00:00' # AND orders.delivery_at < '$max_delivery_time'");
                    $rs = $query->fetchAll(PDO::FETCH_ASSOC);


                    # If found any matches, record the delivery to this sender
                    if(!empty($rs)):

                        foreach($rs as $row):
                            # Checks if there are many deliveries to the same receiver
                            $query = $this->db
                                ->query("SELECT id
                                FROM orders
                                WHERE id_receiver = {$row['id_receiver']} AND has_sender IS NULL AND no_delivery != 1");
                            $rs = $query->fetchAll(PDO::FETCH_ASSOC);


                            foreach($rs as $row_receiver):
                                $data = array(':id_sender' => $current_sender, ':id_order' => $row_receiver['id']);
                                $query = $this->db
                                    ->prepare("INSERT INTO deliveries
                                        (id_sender, id_order) VALUES (:id_sender, :id_order)");
                                $query->execute($data);


                                $query = $this->db
                                    ->prepare("UPDATE orders
                                        SET has_sender = true WHERE id_receiver = {$row['id_receiver']} AND orders.no_delivery != 1");
                                $query->execute();
                            endforeach;


                            $count_deliveries--;
                            if($count_deliveries == 0) break 2;

                        endforeach;
                    endif;
                endif;
            endfor;


            if($count_deliveries > 0):
                # Get every order to find which is closest
                $order_markers;

                $query = $this->db
                    ->query("SELECT district, geolat, geolng
                        FROM users
                        WHERE id = $current_sender");
                $origin_district = $query->fetch(PDO::FETCH_ASSOC);

                # Populates the district list without delivery time to find which ones are closest
                $query = $this->db
                    ->query("SELECT DISTINCT(orders.id_receiver), users.district, users.geolat, users.geolng
                    FROM orders
                    INNER JOIN users ON orders.id_receiver = users.id
                    WHERE users.geolat != 0 AND users.geolng != 0 AND orders.has_sender IS NULL AND orders.no_delivery != 1 #AND orders.delivery_at < '$max_delivery_time'");
                $rs = $query->fetchAll(PDO::FETCH_ASSOC);


                # Populates the LatLng array for the Harvesine function
                $i = 0;
                $order_markers = '';
                foreach($rs as $row):
                    $order_markers[$i]['lat'] = $row['geolat'];
                    $order_markers[$i]['lng'] = $row['geolng'];
                    $order_markers[$i]['id'] = $row['id_receiver'];
                    $order_markers[$i]['district'] = $row['district'];
                    $i++;
                endforeach;


                # Compares with the available orders
                $harvesine = $this->harvesine($order_markers, $origin_district['geolat'], $origin_district['geolng']);



                # Fills the remaining deliveries for the sender with the closest address from his/her home
                foreach($harvesine as $id_receiver => $distance):
                    $query = $this->db
                        ->query("SELECT id as id_order
                        FROM orders
                        WHERE id_receiver = $id_receiver AND has_sender IS NULL AND orders.no_delivery != 1 # AND orders.delivery_at < '$max_delivery_time'");
                    $rs = $query->fetchAll(PDO::FETCH_ASSOC);


                    foreach($rs as $row_receiver):
                        if(!empty($row_receiver)):
                            $data = array(':id_sender' => $current_sender, ':id_order' => $row_receiver['id_order']);
                            $query = $this->db
                                ->prepare("INSERT INTO deliveries
                                    (id_sender, id_order) VALUES (:id_sender, :id_order)");
                            $query->execute($data);

                            $query = $this->db
                                ->prepare("UPDATE orders
                                    SET has_sender = true WHERE id_receiver = {$id_receiver} AND orders.no_delivery != 1");
                            $query->execute();

                            $count_deliveries--;
                        endif;

                        if($count_deliveries == 0) break 2;

                    endforeach;
                endforeach;
            endif;




/*
            $query = $this->db
                ->query("SELECT deliveries.id_sender, MIN(orders.delivery_at) AS start_time
                    FROM deliveries
                    INNER JOIN orders ON deliveries.id_order = orders.id
                    WHERE id_sender = $current_sender #AND orders.delivery_at != '00:00:00'");
            $row = $query->fetch(PDO::FETCH_ASSOC);

            $data = array(':id_sender' => $current_sender, ':start_time' => $row['start_time']);
            $this->db
                ->prepare("INSERT INTO senders (id, start_time)
                    VALUES (:id_sender, :start_time)
                      ON DUPLICATE KEY UPDATE start_time = :start_time")
                ->execute($data);
*/

            $modulus--;
        endforeach;
    }





    # Updates if the product is already collected
    public function collected(){
        $order_id = $_GET['id'];
        $collected = $_GET['collected'];
        $query = $this->db
            ->query("UPDATE orders
                SET collected = $collected
                WHERE id = $order_id");
        die();
    }





    # Calculates how many products are still left to sell
    public function countSupply(){
        $query = $this->db
            ->query("SELECT id FROM products");
        $product_array = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($product_array as $product_id):
            $query = $this->db
                ->query("SELECT COUNT(1) as products_sold, products.name, products.supply
                    FROM orders
                    INNER JOIN products ON orders.id_product = products.id
                    WHERE id_product = {$product_id['id']}");
            $row = $query->fetch(PDO::FETCH_ASSOC);
            $stock = $row['supply'] - $row['products_sold'];
            echo "<p>{$row['name']} - {$row['products_sold']} / {$row['supply']}. Restam $stock em estoque.</p>";
        endforeach;
    }





    # Deletes permanently a user
    public function delete(){
        @$this->db
            ->prepare("DELETE FROM {$_GET['delete']} WHERE id = {$_GET['id']}")
            ->execute();
        die();
    }





    # Deletes permanently a user
    public function deleteDelivery(){
        @$this->db
            ->prepare("DELETE FROM {$_GET['deleteDelivery']} WHERE id = {$_GET['id']} AND id_delivery = {$_GET['id_delivery']}")
            ->execute();
        die();
    }





    # Deletes permanently a problem
    public function deleteProblem(){
        @$this->db
            ->prepare("UPDATE problems SET is_resolved = 1 WHERE id = {$_GET['deleteProblem']}")
            ->execute();
        header('location: index.php?allDeliveries=problems');
    }






    # Returns a distance array from an address to many others
    public function harvesine($markers_array, $origin_lat, $origin_lng) {
        $lat1 = $origin_lat; # latitudinal value to compare
        $lng1 = $origin_lng; # longitudinal value to compare
        $pi = pi();
        $R = 6371; # equatorial radius
        $distances = array();

        foreach($markers_array as $marker):
            # Harvesine formula
            $lat2 = $marker['lat'];
            $lng2 = $marker['lng'];

            $chlat = $lat2-$lat1;
            $chlng = $lng2-$lng1;

            $dlat = $chlat * ($pi/180);
            $dlng = $chlng * ($pi/180);

            $rlat1 = $lat1 * ($pi/180);
            $rlat2 = $lat2 * ($pi/180);

            $a = sin($dlat/2) * sin($dlat/2) + sin($dlng/2) * sin($dlng/2) * cos($rlat1) * cos($rlat2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $d = $R * $c;

            # Generate the array of distances between the starting point and every other address on the list
            $distances[$marker['id']] = $d;
        endforeach;

        # Order the closest places first
        asort($distances);

        # Get only the X most closest results
        $distances = array_slice($distances, 0, 5, true);
        # Prepares the closest places in order for the SQL
        $sql_in = implode(', ', array_keys($distances));
        return $distances;
    }





    # Shows the sheet of the deliveries specific to a sender
    public function deliveries(){
        $query = $this->db
            ->query("SELECT orders.id as 'order_id', orders.is_paid, orders.card, orders.card_from, orders.card_to, orders.delivery_at, p1.name as 'product_name',
                u1.name as 'seller_name', u1.phone as 'seller_phone', u1.mobile as 'seller_mobile',
                u2.name as 'buyer_name', u2.phone as 'buyer_phone', u2.mobile as 'buyer_mobile',
                u3.id as 'gifted_id', u3.name as 'gifted_name', u3.phone as 'gifted_phone', u3.mobile as 'gifted_mobile', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province
                FROM orders
                INNER JOIN users as u1 ON orders.id_seller = u1.id
                INNER JOIN users as u2 ON orders.id_buyer = u2.id
                INNER JOIN users as u3 ON orders.id_receiver = u3.id
                INNER JOIN products as p1 ON orders.id_product = p1.id
                INNER JOIN deliveries ON deliveries.id_order = orders.id
                WHERE orders.no_delivery = 0 AND deliveries.id_sender = {$_SESSION['id']}");
        $deliveries = $query->fetchAll(PDO::FETCH_ASSOC);


        if(!empty($deliveries)): ?>
    <div id="list">
    <h1>Minhas entregas</h1>
        <ul>
            <li>
                <label class="name">Vendedor</label>
                <label class="name">Comprador</label>
                <label class="name">Presenteado</label>
                <label class="full-address">Endereço</label>
                <label class="name">Produto</label>
                <label class="card">Cartão</label>
                <label class="card">Pago</label>
                <label class="notes">Notas</label>
            </li>

            <?php
            foreach($deliveries as $row): ?>

            <li>
                <label class="name"><?php echo $row['seller_name']; ?>
                    <address><?php echo $row['seller_mobile']; ?><?php if(!empty($row['seller_phone']) && !empty($row['seller_mobile'])) echo ' / '; ?><?php echo $row['seller_phone']; ?></address>
                </label>
                <label class="name"><?php echo $row['buyer_name']; ?>
                    <address><?php echo $row['buyer_mobile']; ?><?php if(!empty($row['buyer_phone']) && !empty($row['buyer_mobile'])) echo ' / '; ?><?php echo $row['buyer_phone']; ?></address>
                </label>
                <label class="name"><?php echo $row['gifted_name']; ?>
                    <address><?php echo $row['gifted_mobile']; ?><?php if(!empty($row['gifted_phone']) && !empty($row['gifted_mobile'])) echo ' / '; ?><?php echo $row['gifted_phone']; ?></address>
                </label>
                <label class="full-address">
                    <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                    <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                    <?php echo $row['district']; ?> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?><br />
                </label>
                <label class="name"><?php echo $row['product_name']; ?></label>
                <label class="card tooltip"><?php echo ($row['card']) ? 'Sim' : 'Não';
                    if(!empty($row['card'])) echo "<span>De: {$row['card_from']}<br />Para: {$row['card_to']}</span>"; ?>
                </label>
                <label class="card"><?php echo ($row['is_paid']) ? 'Sim' : 'Não'; ?></label>
                <label class="notes">
                    <p><a class="reportProblem" href="javascript:;" data-id="<?php echo $row['order_id']; ?>"><img src="img/problem.png" alt="" />Relatar problema</a></p>
                </label>
            </li>
            <?php endforeach; ?>
    </ul>
    </div>
        <?php else: ?>
            <div id="list"><h3>Não existe nenhuma encomenda agendada para você ainda.</h3></div>
        <?php endif;
    }





    # Sender went to delivery
    public function delivery_coming(){
        $id = $_GET['id'];
        $delivery_coming = $_GET['delivery_coming'];
        $query = $this->db
            ->query("UPDATE senders
                SET delivery_coming = $delivery_coming, date_finish = NOW()
                WHERE id = $id");
        die();
    }





    # Sender came from delivery
    public function delivery_leaving(){
        $id = $_GET['id'];
        $delivery_leaving = $_GET['delivery_leaving'];
        $query = $this->db
            ->query("UPDATE senders
                SET delivery_leaving = $delivery_leaving, date_beginning = NOW()
                WHERE id = $id");
        die();
    }





    # Generate a CSV file with info from buyers
    public function exportBuyers(){
        $exportCSV = array();
        $exportCSV[] = array("Nome","Telefone","Email");

        $query = $this->db->query("SELECT name, phone, email
            FROM users
            WHERE is_buyer = 1
            ORDER BY name ASC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            $exportCSV[] = array("{$row['name']}","{$row['phone']}","{$row['email']}");
        endforeach;


        $file = fopen("csv/relatorio-compradores.csv", 'w') or die("Unable to open file!");
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach($exportCSV as $fields):
            fputcsv($file, $fields);
        endforeach;
        fflush($file);
        fclose($file);
        echo '<a href="csv/relatorio-compradores.csv">Baixar lista de compradores</a>';
    die();
    }





    # Generate a CSV file with all emails from senders
    public function exportEmailSenders(){
        $exportCSV = array();

        $query = $this->db->query("SELECT email, name
            FROM users
            WHERE is_sender = 1 AND is_active = 1
            ORDER BY email ASC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            $exportCSV[] = array("{$row['email']}");
        endforeach;


        $file = fopen("csv/relatorio-emails.csv", 'w') or die("Unable to open file!");
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach($exportCSV as $fields):
            fputcsv($file, $fields);
        endforeach;
        fflush($file);
        fclose($file);
        echo '<a href="csv/relatorio-emails.csv">Baixar lista de emails</a>';
    die();
    }





    # Generate a CSV file with all addresses from senders
    public function exportSendersAddresses(){
        $exportCSV = array();

        $query = $this->db->query("SELECT name, address, address2, district
            FROM users
            WHERE is_sender = 1 AND is_active = 1
            ORDER BY name ASC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            $exportCSV[] = array("{$row['name']}","{$row['address']} {$row['address2']}","{$row['district']}");
        endforeach;


        $file = fopen("csv/relatorio-endereco-entregadores.csv", 'w') or die("Unable to open file!");
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach($exportCSV as $fields):
            fputcsv($file, $fields);
        endforeach;
        fflush($file);
        fclose($file);
        echo '<a href="csv/relatorio-endereco-entregadores.csv">Baixar lista de endere&ccedil;os dos entregadores</a>';
    die();
    }





    # Print the orders report
    public function exportOrders(){

        // Original PHP code by Chirp Internet: www.chirp.com.au
        // Please acknowledge use of this code by including this header

        function cleanData(&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        }


        $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to, u1.name as 'seller_name', u2.name as 'buyer_name', u3.name as 'gifted_name', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, orders.notes
            FROM orders
            INNER JOIN users as u1 ON orders.id_seller = u1.id
            INNER JOIN users as u2 ON orders.id_buyer = u2.id
            INNER JOIN users as u3 ON orders.id_receiver = u3.id
            ORDER BY orders.created_at DESC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            $card_html = ''; $is_paid_html = ''; $delivery_html = '';
            if($row['card']) $card_html = 'Sim';
            if($row['is_paid']) $is_paid_html = 'Sim';
            if($row['no_delivery']):
                $delivery_html = 'Sim';
            endif;
            $notes = ($row['notes']) ? "[{$row['notes']}]" : '';


            # Generates each row of the sheet
            $exportXLS[] = array("{$row['id']}","{$row['seller_name']}","{$row['buyer_name']}","{$row['gifted_name']}","{$row['address']} {$row['address2']} $reference","{$row['district']}","{$row['city']} - {$row['province']}","De: {$row['card_from']} - Para: {$row['card_to']}",$card_html,$is_paid_html,$delivery_html,$notes);
        endforeach;



        # Print the data on Excel sheet
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment; filename=relatorio-venda.xls');
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');

        $header_titles = array('id','Vendedor','Comprador','Presenteado','Endereço','Bairro','Cidade','Etiqueta','Cartão','Pago','Sem entrega','Notas');
        echo implode("\t", $header_titles) . "\n";

        foreach($exportXLS as $row) {
            array_walk($row, 'cleanData');
            echo implode("\t", array_values($row)) . "\n";
        }
        die();

    die();
    }








    # Export the seller report to a CSV file
    public function exportSellerReport(){
        $id_seller = (isset($_GET['exportSellerReport']) && $_SESSION['type'] == 'admin') ? $_GET['exportSellerReport'] : $_SESSION['id'];

        $exportCSV = array();
        $exportCSV[] = array("id","Comprador","Presenteado","Endereço","Bairro","Cidade","Etiqueta","Cartão","Pago","Notas");

        $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, /* orders.delivery_at, */ orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to, u2.name as 'buyer_name', u3.name as 'gifted_name', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, orders.notes, d1.id_sender, u4.name as 'sender_name'
                FROM orders
                LEFT JOIN users as u2 ON orders.id_buyer = u2.id
                LEFT JOIN users as u3 ON orders.id_receiver = u3.id
                LEFT JOIN deliveries as d1 ON orders.id = d1.id_order
                LEFT JOIN users as u4 ON d1.id_sender = u4.id
                WHERE id_seller = $id_seller
                ORDER BY u2.name ASC, u3.name ASC");


        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            $card_html = ''; $is_paid_html = ''; $delivery_html = '';
            $reference = ($row['reference']) ? "({$row['reference']})" : '';
            if($row['card']) $card_html = 'Sim';
            if($row['is_paid']) $is_paid_html = 'Sim';
            if($row['no_delivery']):
                $delivery_html = 'Sem entrega';
/*
            else:
                if($row['delivery_at'] != '00:00:00') $delivery_html = 'Entregar às ' . substr($row['delivery_at'],0,-3);
*/
            endif;

            # Generates each row of the sheet
            $exportCSV[] = array("{$row['id']}","{$row['buyer_name']}","{$row['gifted_name']}","{$row['address']} {$row['address2']} $reference","{$row['district']}","{$row['city']} - {$row['province']}","De: {$row['card_from']} - Para: {$row['card_to']}","$card_html","$is_paid_html","$delivery_html","{$row['notes']}");
        endforeach;


        $file = fopen("csv/relatorio-vendedor-$id_seller.csv", 'w');

        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach($exportCSV as $fields):
            fputcsv($file, $fields);
        endforeach;
        fclose($file);
        echo '<a href="csv/relatorio-vendedor-' . $id_seller . '.csv">Baixar planilha</a>';
    die();
    }





    # Find deliveries close to some address
    public function findHarvesine(){
//        if(!empty($_GET['geolat'])):
//            $query = $this->db
//                ->query("SELECT DISTINCT(orders.id_receiver), orders.id AS id_order, users.name, users.address, users.district, users.geolat, users.geolng
//                        FROM orders
//                        INNER JOIN users ON orders.id_receiver = users.id
//                        WHERE users.geolat != 0 AND users.geolng != 0 AND orders.no_delivery != 1");
//            $rs = $query->fetchAll(PDO::FETCH_ASSOC);
//
//            # Populates the LatLng array for the Harvesine function
//            $i = 0;
//            $order_markers = '';
//            foreach($rs as $row):
//                $order_markers[$i]['lat'] = $row['geolat'];
//                $order_markers[$i]['lng'] = $row['geolng'];
//                $order_markers[$i]['id'] = $row['id_receiver'];
//                $order_markers[$i]['name'] = $row['name'];
//                $order_markers[$i]['address'] = $row['address'];
//                $order_markers[$i]['district'] = $row['district'];
//                $i++;
//            endforeach;
//
//            $lat1 = $_GET['geolat']; # latitudinal value to compare
//            $lng1 = $_GET['geolng']; # longitudinal value to compare
//            $pi = pi();
//            $R = 6371; # equatorial radius
//            $distances = array();
//
//            foreach($order_markers as $marker):
//                # Harvesine formula
//                $lat2 = $marker['lat'];
//                $lng2 = $marker['lng'];
//
//                $chlat = $lat2-$lat1;
//                $chlng = $lng2-$lng1;
//
//                $dlat = $chlat * ($pi/180);
//                $dlng = $chlng * ($pi/180);
//
//                $rlat1 = $lat1 * ($pi/180);
//                $rlat2 = $lat2 * ($pi/180);
//
//                $a = sin($dlat/2) * sin($dlat/2) + sin($dlng/2) * sin($dlng/2) * cos($rlat1) * cos($rlat2);
//                $c = 2 * atan2(sqrt($a), sqrt(1-$a));
//                $d = $R * $c;
//
//                # Generate the array of distances between the starting point and every other address on the list
//                $distances[$marker['id']] = array(
//                        'distance' => $d,
//                        'id' => $marker['id'],
//                        'name' => $marker['name'],
//                        'address' => $marker['address'],
//                        'district' => $marker['district']
//                    );
//            endforeach;
//
//            # Order the closest places first
//            asort($distances);
//
//            # Get only the X most closest results
//            $distances = array_slice($distances, 0, 10, true);
//            # Prepares the closest places in order for the SQL
//            $sql_in = implode(', ', array_keys($distances));
//        endif;
        echo 'oi';
        ?>
        <div id="list">
            <div id="map-canvas" style="display:none"></div>

            <h1>Entregas próximas</h1>
            <form action="index.php?getUserGeo" id="form-get-user-geo" method="post">
                <input class="autocomplete-name" id="name" name="name" placeholder="Buscar usuário" type="search">
                <input type="submit" value="Procurar por usuário">
            </form>

            <form action="index.php?findHarvesine" id="form-verify-geo2" method="get">
                <input id="address" placeholder="Av Afonso Pena, 101" type="search">
                <input type="submit" value="Procurar por endereço">
            </form>

            <ul>
                <li>
                    <label class="id">Id</label>
                    <label class="name">Nome</label>
                    <label class="full-data">Endereço</label>
                    <label class="name">Bairro</label>
                    <label class="notes">Distância</label>
                </li>

                <?php foreach($distances as $row): ?>

                <li>
                    <label class="id"><?php echo $row['id']; ?></label>
                    <label class="name"><?php echo $row['name']; ?></label>
                    <label class="full-data"><?php echo $row['address']; ?></label>
                    <label class="name"><?php echo $row['district']; ?></label>
                    <label class="notes"><?php echo $row['distance']; ?></label>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php
    }





    # Sends reminder
    public function forgetPassword(){
        $data = array(':email' => $_POST['forgotten-email']);
        $query = $this->db
            ->prepare("SELECT id, name
                FROM users
                WHERE email = :email AND is_seller = 1");
            $query->execute($data);

        if($row = $query->fetch(PDO::FETCH_ASSOC)):
            require_once ('PHPMailer/class.phpmailer.php'); // Add the path as appropriate
            $Mail = new PHPMailer();
            $Mail->IsSMTP(); // Use SMTP
            $Mail->Host        = "smtp.eduardofilizzola.com"; // Sets SMTP server
            $Mail->SMTPDebug   = 0; // 2 to enable SMTP debug information
            $Mail->SMTPAuth    = TRUE; // enable SMTP authentication
            $Mail->Port        = 587; // set the SMTP port
            $Mail->Username    = 'admin@eduardofilizzola.com'; // SMTP account username
            $Mail->Password    = 'SU1e2d3u'; // SMTP account password
            $Mail->Priority    = 1; // Highest priority - Email priority (1 = High, 3 = Normal, 5 = low)
            $Mail->CharSet     = 'UTF-8';
            $Mail->Subject     = 'Redefinir a senha';
            $Mail->ContentType = 'text/html; charset=utf-8\r\n';
            $Mail->From        = 'admin@eduardofilizzola.com';
            $Mail->FromName    = 'Site CICX';
            $Mail->AddAddress($_POST['forgotten-email']); // To:
            $Mail->isHTML(TRUE);
            $Mail->Body        = 'Clique no link a seguir para redefinir sua senha:<br />
<a href="http://www.eduardofilizzola.com/cicx/index.php?resetPasswordEmail=' . $row['id'] . '">http://www.eduardofilizzola.com/cicx/index.php?resetPasswordEmail=' . $row['id'] . '</a>';
            $Mail->Send();
            $Mail->SmtpClose();

            //if ( $Mail->IsError() ) { echo "ERROR<br /><br />"; } else { echo "OK<br /><br />"; }
            header('location: index.php?forget=1');
        else:
            header('location: index.php?error=forget');
        endif;

    }






    # Generates the autocomplete list for districts
    public function formAddOrder(){

        $buyer = (isset($_GET['buyer'])) ? $_GET['buyer'] : '';
        $gifted = (isset($_GET['gifted'])) ? $_GET['gifted'] : '';
        ?>
        <div id="list">
            <h2><img src="img/add-order.png" alt="Adicionar venda" title="Adicionar venda" /> Adicionar nova venda</h2>

            <form action="index.php?verifyBuyer" class="first-step" id="form-add-order" method="post">
                <input class="autocomplete-buyer" id="buyer" name="buyer" placeholder="Nome do comprador" tabindex="1" type="text" required value="<?php echo $buyer; ?>" /> <a href="javascript:;" class="new-buyer" tabindex="4">Adicionar comprador</a><br />

                <input class="autocomplete-gifted" id="gifted" name="gifted" placeholder="Nome do presenteado" tabindex="2" type="text" required value="<?php echo $gifted; ?>" /> <a href="javascript:;" class="new-gifted" id="new-gifted" tabindex="5">Adicionar presenteado</a><br />
                <label><input id="no_delivery" name="no_delivery" type="checkbox" onchange="if($('#no_delivery').is(':checked')){$('#gifted').val('Sem entrega');}else{$('#gifted').val('')}" /> Sem entrega</label>
<!--                <a href="javascript:;" id="buyer-is-gifted">Comprador é presenteado</a>--><br /><br />
                <input type="submit" tabindex="3" value="Validar venda" />
            </form>



            <form action="index.php?addUser=1&return=1&buyer=<?php echo $buyer; ?>&gifted=<?php echo $gifted; ?>" id="form-add-buyer" method="post">

                <div id="map-canvas"></div>
                <h3>Adicionar comprador</h3>
                <input id="name" name="name" placeholder="Nome do comprador" required type="text" />
                <input id="email" name="email" placeholder="Email" type="email" />
                <input id="password" name="password" placeholder="Senha" type="hidden" value="" /><br />
                <input class="mobile" id="mobile" name="mobile" placeholder="Telefone principal" required type="text" />
                <input class="validate-phone" id="phone" name="phone" placeholder="Outro telefone" type="text" /><br>
                <input class="gifted-address" id="address" name="address" placeholder="Endereço" type="text" />
                <input cla ss="gifted-address" id="address2" name="address2" placeholder="Complemento" type="text" />
                <input id="reference" name="reference" placeholder="Referência" type="text" />
                <input class="district gifted-address" id="district" name="district" placeholder="Bairro" type="hidden" />
<!--				<input class="gifted-address" id="city" name="city" placeholder="Cidade" type="text" />-->
                <input class="gifted-address" id="city" name="city" type="hidden">
<!--
                    <option disabled="disabled" selected="selected">Cidade</option>
                    <option>Belo Horizonte</option>
                    <option>Betim</option>
                    <option>Contagem</option>
                    <option>Lagoa Santa</option>
                    <option>Nova Lima</option>
                    <option>Ribeirão das Neves</option>
                    <option>Sabará</option>
                    <option>Santa Luzia</option>
                </select>
-->
                <input class="gifted-address" id="province" name="province" placeholder="UF" type="hidden" value="MG" /><br>
                <input id="notes" name="notes" placeholder="Notas" type="text" /><br>

                <input id="geolat" name="geolat" type="hidden" />
                <input id="geolng" name="geolng" type="hidden" />

                <input id="is_buyer" name="is_buyer" type="hidden" value="on" />
                <input id="is_order" name="is_order" type="hidden" value="yes" />
                <input id="remember-gifted-name" name="remember-gifted-name" type="hidden" value="<?php echo $gifted; ?>" />
                <input type="submit" value="Salvar comprador" />
            </form>


            <form action="index.php?addUser=1&return=1&buyer=<?php echo $buyer; ?>&gifted=<?php echo $gifted; ?>" id="form-add-gifted" method="post">
                <div id="gifted-map-canvas"></div>
                <h3>Adicionar presenteado</h3>
                <input id="gifted-name" name="gifted-name" placeholder="Nome do presenteado" required type="text" />
                <input id="gifted-email" name="gifted-email" placeholder="Email" type="email" />
                <input id="gifted-password" name="gifted-password" placeholder="Senha" type="hidden" value="" /><br />
                <input class="mobile" id="gifted-mobile" name="gifted-mobile" placeholder="Telefone principal" required type="text" />
                <input class="validate-phone" id="gifted-phone" name="gifted-phone" placeholder="Outro telefone" type="text" /><br>
                <input class="gifted-address" id="gifted-address" name="gifted-address" placeholder="Endereço" required type="text" />
                <input cla ss="gifted-address" id="gifted-address2" name="gifted-address2" placeholder="Complemento" type="text" />
                <input id="gifted-reference" name="gifted-reference" placeholder="Referência" type="text" /><br />
                <input class="district gifted-address" id="gifted-district" name="gifted-district" placeholder="Bairro" required type="hidden" />
<!--				<input class="gifted-address" id="gifted-city" name="gifted-city" placeholder="Cidade" required type="text" />-->
                <input class="gifted-address" id="gifted-city" name="gifted-city" type="hidden">
<!--
                    <option disabled="disabled" selected="selected">Cidade</option>
                    <option>Belo Horizonte</option>
                    <option>Betim</option>
                    <option>Contagem</option>
                    <option>Lagoa Santa</option>
                    <option>Nova Lima</option>
                    <option>Ribeirão das Neves</option>
                    <option>Sabará</option>
                    <option>Santa Luzia</option>
                </select>
-->
                <input class="gifted-address" id="gifted-province" name="gifted-province" placeholder="UF" type="hidden" value="MG" />
                <input id="gifted-notes" name="gifted-notes" placeholder="Notas" type="text" /><br />

                <input id="gifted-geolat" name="gifted-geolat" type="hidden" />
                <input id="gifted-geolng" name="gifted-geolng" type="hidden" />

                <input id="is_gifted" name="is_gifted" type="hidden" value="on" />
                <input id="is_order" name="is_order" type="hidden" value="yes" />
                <input id="remember-buyer-name" name="remember-buyer-name" type="hidden" value="<?php echo $buyer; ?>" />

                <input type="submit" value="Salvar presenteado" />
                <div class="clearfix"></div>
            </form>
        </div>
    <?php
    }






    # Generates the autocomplete list for districts
    public function getDistricts(){
        $data = array(':district' => '%' . $_GET['getDistricts'] . '%');
        $query = $this->db
            ->prepare("SELECT district as value
                FROM districts
                WHERE district LIKE :district ORDER BY district ASC");
        $query->execute($data);
        $row = $query->fetchAll(PDO::FETCH_ASSOC);

        header('Content-type: application/json');
        echo json_encode( array('suggestions'=> $row ) );
        die();
    }





    # Generates the autocomplete list for names with deliveries
    public function getGiftedNames(){
        $data = array(':name' => '%' . $_GET['getGiftedNames'] . '%');
        $query = $this->db
            ->prepare("SELECT CONCAT(name, '<span> ', mobile, ' - ', district, '</span>') as value, name
                FROM users
                INNER JOIN orders ON orders.id_receiver = users.id
                WHERE users.name LIKE :name AND orders.id_receiver = users.id
                GROUP BY users.id
                HAVING users.id NOT IN (SELECT key_senders.id_delivery FROM key_senders WHERE key_senders.id_delivery IS NOT NULL)
                ORDER BY name ASC");
        $query->execute($data);
        $row = $query->fetchAll(PDO::FETCH_ASSOC);

        header('Content-type: application/json');
        echo json_encode( array('suggestions'=> $row ) );
        die();
    }







    # Generates the autocomplete list for names
    public function getNames(){
        $data = array(':name' => '%' . $_GET['getNames'] . '%');
        $query = $this->db
            ->prepare("SELECT CONCAT(name, '<span> ', mobile, ' - ', district, '</span>') as value, name
                FROM users
                WHERE name LIKE :name ORDER BY name ASC");
        $query->execute($data);
        $row = $query->fetchAll(PDO::FETCH_ASSOC);

        header('Content-type: application/json');
        echo json_encode( array('suggestions'=> $row) );
        die();
    }





    # Generates the autocomplete list for names of buyers
    public function getNamesBuyer(){
        $data = array(':name' => '%' . $_GET['getNamesBuyer'] . '%');
        $query = $this->db
            ->prepare("SELECT CONCAT(name, '<span> ', mobile, ' - ', district, '</span>') as value, name
                FROM users
                WHERE name LIKE :name AND is_buyer = 1 ORDER BY name ASC");
        $query->execute($data);
        $row = $query->fetchAll(PDO::FETCH_ASSOC);

        header('Content-type: application/json');
        echo json_encode( array('suggestions'=> $row) );
        die();
    }





    # Generates the autocomplete list for names of gifted
    public function getNamesGifted(){
        $data = array(':name' => '%' . $_GET['getNamesGifted'] . '%');
        $query = $this->db
            ->prepare("SELECT CONCAT(name, '<span> ', mobile, ' - ', district, '</span>') as value, name
                FROM users
                WHERE name LIKE :name AND is_gifted = 1 ORDER BY name ASC");
        $query->execute($data);
        $row = $query->fetchAll(PDO::FETCH_ASSOC);

        header('Content-type: application/json');
        echo json_encode( array('suggestions'=> $row) );
        die();
    }





    # Generates the autocomplete list for senders
    public function getNamesSender(){
        $data = array(':name' => '%' . $_GET['getNamesSender'] . '%');
        $query = $this->db
            ->prepare("SELECT CONCAT(name, '<span> ', mobile, ' - ', district, '</span>') as value, name
                FROM users
                WHERE name LIKE :name AND is_sender = 1 ORDER BY name ASC");
        $query->execute($data);
        $row = $query->fetchAll(PDO::FETCH_ASSOC);

        header('Content-type: application/json');
        echo json_encode( array('suggestions'=> $row) );
        die();
    }





    # Finds geo coordinates from specific user and redirects to
    public function getUserGeo(){
        $query = $this->db
            ->query("SELECT geolat, geolng FROM users WHERE name = '" . $_POST['name'] . "'");
        $rs = $query->fetchAll(PDO::FETCH_ASSOC);
        header('location: index.php?findHarvesine&geolat=' . $rs[0]['geolat'] . '&geolng=' . $rs[0]['geolng']);
    }





    # Lets admin select which deliveries each key sender should make
    public function keySender(){
        $query = $this->db->query("SELECT id, name
            FROM users
            WHERE users.id = " . $_GET['keySender']);

        $row = $query->fetch(PDO::FETCH_ASSOC);
    ?>
    <div id="list">
        <h1><img src="img/key-senders.png" alt="Ver entregadores-chave" title="Ver entregadores-chave" /> Distribuir entregas para <?php echo $row['name']; ?></h1>
        <a href="index.php?listKeySenders" class="button">Voltar aos entregadores-chave</a>

        <form action="index.php?addKeySender" method="post">
            <input class="autocomplete-delivery" id="name" name="name" placeholder="Nome" type="text" required />
            <input id="id" name="id" type="hidden" value="<?php echo $row['id']; ?>"/>
            <input type="submit" value="Adicionar entrega" />
        </form>
        <ul>
            <li>
                <label class="id">Id</label>
                <label class="name">Presenteado</label>
                <label class="full-data">Endereço</label>
                <label class="notes">Horário</label>
            </li>

            <?php
            $query = $this->db->query("SELECT DISTINCT(users.id), users.name, users.address, users.address2, users.reference, users.district, users.city, users.province, orders.delivery_at
                FROM users
                INNER JOIN key_senders ON users.id = key_senders.id_delivery
                INNER JOIN orders ON orders.id_receiver = key_senders.id_delivery
                WHERE key_senders.id = {$_GET['keySender']}
                ORDER BY users.name ASC, orders.delivery_at DESC");

            foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            ?>

            <li id="key_senders-<?php echo $row['id']; ?>">
                <label class="id"><?php echo $row['id']; ?></label>
                <label class="name"><?php echo $row['name']; ?></label>
                <label class="full-data">
                    <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                    <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                    <strong><?php echo $row['district']; ?></strong> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?>
                </label>

                <a href="javascript:;" class="doDeleteDelivery" data-id="<?php echo $_GET['keySender']; ?>" data-delivery="<?php echo $row['id']; ?>" data-type="key_senders"><img src="img/remove.png" alt="" /> Deletar</a>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php
    }





    # Updates if the sender is available for deliveries
    public function is_active(){
        $sender_id = $_GET['id'];
        $is_active = $_GET['is_active'];
        $query = $this->db
            ->query("UPDATE users
                SET is_active = $is_active
                WHERE id = $sender_id");
        die();
    }





    # Updates when the senders' phone and address are confirmed
    public function is_confirmed(){
        $user_id = $_GET['id'];
        $is_confirmed = $_GET['is_confirmed'];
        $query = $this->db
            ->query("UPDATE users
                SET is_confirmed = $is_confirmed
                WHERE id = $user_id");
        die();
    }





    # Updates if the order is already paid
    public function is_paid(){
        $order_id = $_GET['id'];
        $is_paid = $_GET['is_paid'];
        $query = $this->db
            ->query("UPDATE orders
                SET is_paid = $is_paid
                WHERE id = $order_id");
        die();
    }






    # Populates the list of districts with each LatLng coordinate
    public function latlngdistrict(){
    if($_POST):
        $data = array(
            ':id' => $_POST['id'],
            ':geolat' => $_POST['geolat'],
            ':geolng' => $_POST['geolng']);
        $query = $this->db
            ->prepare("UPDATE districts
                SET geolat = :geolat, geolng = :geolng WHERE id = :id");
        $query->execute($data);
    endif;


        $query = $this->db
                ->query("SELECT id, district, city
                FROM districts
                WHERE geolat IS NULL AND geolng IS NULL LIMIT 1");
            $row = $query->fetch(PDO::FETCH_ASSOC); ?>

<!DOCTYPE html>
<html id="map">
<head>
    <meta charset="utf-8">
    <title>Mapa da localidade - Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="jquery.js" type="text/javascript"></script>
    <link rel="stylesheet" href="style.css" />
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkK3zL6elimsbHUpGJf4eZn47QoKcRyaQ&sensor=true&language=pt-BR"></script>
<script type="text/javascript">
var geocoder;
var map;
function initialize() {
  geocoder = new google.maps.Geocoder();
  var latlng = new google.maps.LatLng(-20.191261303342795, -44.372534660937504);
  var mapOptions = {
    zoom: 16,
    center: latlng,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  }

  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

}


var boundsw = new google.maps.LatLng(-20.191261303342795, -44.372534660937504);
var boundne = new google.maps.LatLng(-19.54549209672402, -43.685889153125004);
var bounds = new google.maps.LatLngBounds(boundsw, boundne);


function codeAddress() {
  var address = '<?php echo $row['district'] . ', ' . $row['city']; ?>';
  geocoder.geocode( { 'address': address, 'bounds': bounds }, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      map.setCenter(results[0].geometry.location);
      var marker = new google.maps.Marker({
          map: map,
          position: results[0].geometry.location
      });
      $('#geolat').val(results[0].geometry.location.lat());
      $('#geolng').val(results[0].geometry.location.lng());
      $('#submit').click();
    } else {
      alert('Geocode was not successful for the following reason: ' + status);
    }
  });
}


google.maps.event.addDomListener(window, 'load', initialize);
google.maps.event.addDomListener(window, 'load', codeAddress);
</script>
</head>
<body>
<form action="index.php?latlngdistrict" id="form1" method="post">
    <input id="id" name="id" type="text" value="<?php echo $row['id']; ?>" />
    <input id="geolat" name="geolat" type="text" value="" />
    <input id="geolng" name="geolng" type="text" value="" />
    <input id="submit" type="submit" />
</form>
    <div id="map-canvas" style="height: 200px !important; width: 200px !important"></div>
</body>
</html>


<?php
        die();
    }




    # Shows all users
    public function listAll(){
        $query_sender = ''; $count_users_active = '';
        if(isset($_GET['listAll'])) $group = $_GET['listAll'];
        switch ($group):
            case 'is_seller': $user_type_label = 'vendedores'; $img_src = 'sellers'; break;
            case 'is_buyer': $user_type_label = 'compradores'; $img_src = 'buyers'; break;
            case 'is_sender': $user_type_label = 'entregadores'; $img_src = 'senders';
            $query_sender = ', (SELECT count(1) FROM deliveries WHERE deliveries.id_sender = users.id) as count_deliveries'; break;
            case 'is_gifted': $user_type_label = 'presenteados'; $img_src = 'gifteds'; break;
        endswitch;

        $count_users_rs = $this->db->query("SELECT COUNT(1) FROM users WHERE $group = 1");
        $count_users = $count_users_rs->fetchColumn();

        if($group == 'is_sender' && !isset($_GET['notActive'])):
            $count_users_active_rs = $this->db->query("SELECT COUNT(1) FROM users WHERE is_sender = 1 AND is_active = 1");
            $count_users_active = $count_users_active_rs->fetchColumn();
        endif;
        $count_users_msg = ($count_users_active) ? "($count_users_active / $count_users)" : "({$count_users})";
        ?>
    <div id="list">
        <h1><img src="img/<?php echo $img_src; ?>.png" alt="Ver <?php echo $user_type_label; ?>" title="Ver <?php echo $user_type_label; ?>" /> Listando usuários <?php if(!isset($_GET['notActive'])) echo ' ativos '; ?>  <?php if(isset($user_type_label)) echo "que são $user_type_label $count_users_msg"; ?></h1>

        <?php if($group == 'is_sender'): ?>
            <?php if(!isset($_GET['notActive'])): ?>
                <a href="index.php?listAll=is_sender&notActive" class="button">Listar todos entregadores</a>
            <?php else: ?>
                <a href="index.php?listAll=is_sender" class="button">Listar somente ativos</a>
            <?php endif; ?>

            <a href="index.php?exportEmailSenders" class="button" target="_blank">Gerar lista de email dos entregadores</a>
            <a href="index.php?exportSendersAddresses" class="button" target="_blank">Gerar lista de endereço dos entregadores</a>
            <a href="index.php?findHarvesine" class="button">Encontrar entregas próximas</a>
            <a href="index.php?redistributeDeliveries" class="button">Redistribuir as entregas</a>
        <?php endif; ?>
        <?php if($_SESSION['type'] == 'admin' && $group == 'is_buyer'): ?>
            <a href="index.php?exportBuyers" class="button" target="_blank">Gerar lista de compradores</a>
        <?php endif; ?>
        <?php if(isset($_GET['error'])) echo '<p class="error">Nenhum usuário encontrado</p>'; ?>
        <form action="index.php?type=<?php echo $_GET['listAll']; ?>" method="post">
            <input id="search" name="search" placeholder="Buscar <?php echo $user_type_label; ?>" type="search" />
            <input type="submit" value="Buscar" />
        </form>
        <ul>
            <li class="sticky">
                <?php if($group == 'is_sender') echo '<label class="id">Ativo</label>'; ?>
                <label class="id">Id</label>
                <label class="name">Usuário</label>
                <label class="email">Email</label>
                <label class="phone">Telefone</label>
                <?php if($group == 'is_sender') echo '<label class="id"><a href="javascript:;" id="reset-is-confirmed">Dados ok</a></label>'; ?>
                <label class="full-address">Endereço</label>
                <label class="notes">Notas</label>
                <?php if($group == 'is_sender') echo '<label class="id"><a href="javascript:;" id="reset-sheet-printed">Ficha impressa</a></label>'; ?>
            </li>
        <?php
        $query_group = ($group) ? "WHERE $group" : '';
        if($group == 'is_sender'):
            if(!isset($_GET['notActive'])):
                $query_group = $query_group . ' AND is_active = 1 ';
            endif;
        endif;
        $query = $this->db->query("SELECT * $query_sender FROM users $query_group ORDER BY name ASC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
        ?>
            <li id="users-<?php echo $row['id']; ?>">
                <?php if($group == 'is_sender'): ?>
                <label class="id"><input class="is-active" <?php if($row['is_active']) echo 'checked="checked"'; ?> data-id="<?php echo $row['id']; ?>" type="checkbox"></label>
                <?php endif; ?>
                <label class="id"><?php echo $row['id']; ?></label>
                <label class="name"><?php echo $row['name']; ?></label>
                <label class="email tooltip"><?php if(!empty($row['email'])): ?><img src="img/email.png" alt="" />
                    <address><?php echo $row['email']; ?></address><?php endif; ?>
                </label>
                <label class="phone"><?php echo ($row['mobile'] == '(00) 0000-0000') ? 'Usar telefone do comprador' : $row['mobile']; ?><?php if(!empty($row['phone']) && !empty($row['mobile'])) echo ' / '; ?><?php echo $row['phone']; ?></label>
                <?php if($group == 'is_sender'): ?>
                <label class="id"><input class="is-confirmed" <?php if($row['is_confirmed']) echo 'checked="checked"'; ?> data-id="<?php echo $row['id']; ?>" type="checkbox"></label>
                <?php endif; ?>

                <label class="full-address tooltip"><?php if(!empty($row['district'])): ?>Ver endereço
                    <address>
                    <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                    <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                    <?php echo $row['district']; ?> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?>
                    </address><?php endif; ?>
                </label>

                <label class="notes">
                    <?php if($row['notes']) echo $row['notes']; ?>
                </label>
                <?php if($group == 'is_sender'):
                $is_sheet_printed = ($row['sheet_printed']) ? 1 : 0;
                $is_sheet_printed_class = ($row['sheet_printed']) ? '' : 'off'; ?>
                <label class="id">
                    <span class="sheet-printed <?php echo $is_sheet_printed_class; ?>" data-active="<?php echo $is_sheet_printed; ?>" data-id="<?php echo $row['id']; ?>"></span>
                </label>
                <?php endif; ?>

                <?php if($_SESSION['type'] != 'user'): ?>
                    <a href="javascript:;" class="doShow" data-id="<?php echo $row['id']; ?>" data-type="users"><img src="img/update.png" alt="" /> Atualizar</a>
                    <a href="javascript:;" class="doDelete" data-id="<?php echo $row['id']; ?>" data-type="users"><img src="img/remove.png" alt="" /> Deletar</a>
                    <?php if($_SESSION['type'] == 'admin' && isset($row['count_deliveries']) && $row['count_deliveries']): ?><a class="see-deliveries" href="index.php?showDeliveries=<?php echo $row['id']; ?>"><img src="img/see-deliveries.gif" alt="" />Ver entregas (<?php echo $row['count_deliveries']; ?>)</a><?php endif; ?>
<!-- 					<?php if($_SESSION['type'] == 'admin' && ($group == 'is_seller')): ?><a class="reset-password" data-id="<?php echo $row['id']; ?>" href="index.php?resetPassword"><img src="img/icon-password.png" alt="" />Redefinir senha</a><?php endif; ?> -->
                    <?php if($_SESSION['type'] == 'admin' && ($group == 'is_seller')): ?><a href="index.php?sellerReport=<?php echo $row['id']; ?>" class="doReport"><img src="img/icon-report.gif" alt="" height="16" width="16" /> Relatório de vendas</a><?php endif; ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
</div>
<?php
    }





    # Shows all keySenders
    public function listKeySenders(){
        if(!isset($_GET['notActive'])):
            $count_users_rs = $this->db->query("SELECT COUNT(DISTINCT key_senders.id) FROM key_senders
            JOIN users ON key_senders.id = users.id
            WHERE key_senders.id_delivery IS NULL AND users.is_active = 1");
        else:
            $count_users_rs = $this->db->query("SELECT DISTINCT COUNT(1) FROM key_senders WHERE id_delivery IS NULL");
        endif;
        $count_users = $count_users_rs->fetchColumn();
    ?>
    <div id="list" class="table-orders">
        <h1><img src="img/key-senders.png" alt="Ver entregadores-chave" title="Ver entregadores-chave" />

        <?php if(!isset($_GET['notActive'])): ?>Listando entregadores-chave ativos
        <?php else: ?>Listando todos entregadores-chave
        <?php endif; ?> (<?php echo $count_users; ?>)</h1>

        <?php if(!isset($_GET['notActive'])): ?>
            <a href="index.php?listKeySenders&notActive" class="button">Listar todos entregadores chave</a>
        <?php else: ?>
            <a href="index.php?listKeySenders" class="button">Listar somente ativos</a>
        <?php endif; ?>


        <ul>
            <li class="sticky">
                <label class="id">Id</label>
                <label class="name">Entregador</label>
                <label class="address">Endereço</label>
            </li>

            <?php
            if(!isset($_GET['notActive'])):
            $query = $this->db->query("SELECT DISTINCT users.id, users.name, users.address, users.address2, users.reference, users.district, users.city
                FROM key_senders
                JOIN users ON users.id = key_senders.id
                WHERE users.is_active = 1
                ORDER BY users.name ASC");
            else:
            $query = $this->db->query("SELECT DISTINCT users.id, users.name, users.address, users.address2, users.reference, users.district, users.city
                FROM key_senders
                INNER JOIN users ON users.id = key_senders.id
                ORDER BY users.name ASC");
            endif;

            foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            ?>

            <li id="key_senders-<?php echo $row['id']; ?>">
                <label class="id"><?php echo $row['id']; ?></label>
                <label class="name"><a href="index.php?keySender=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></label>
                <label class="full-address tooltip">
                    <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                    <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                    <?php echo $row['district']; ?> - <?php echo $row['city']; ?>
                </label>

                <a href="javascript:;" class="doDelete" data-id="<?php echo $row['id']; ?>" data-type="key_senders"><img src="img/remove.png" alt="" /> Deletar</a>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php
    }





    # Shows all orders
    public function listOrders(){
        $count_users_rs = $this->db->query("SELECT COUNT(1) AS count,
            (SELECT COUNT(1) FROM orders) AS count_total
            FROM orders
            WHERE no_delivery = 0");
        $count_users = $count_users_rs->fetchAll();
    ?>
    <div id="list">
    <h1><img src="img/orders.png" alt="Ver vendas" title="Ver vendas" /> Listando vendas com entregas (<?php echo "{$count_users[0]['count']} / {$count_users[0]['count_total']}"; ?>)</h1>

    <?php if(isset($_GET['error'])) echo '<p class="error">Nenhum usuário encontrado</p>'; ?>

    <a href="index.php?printOrders" class="button" target="_blank">Gerar relatório vendas</a>
    <a href="index.php?exportOrders" class="button" target="_blank">Exportar relatório vendas</a>
    <a href="index.php?reportDeliveries" class="button" target="_blank">Exportar relatório entregas/entregadores</a>
    <a href="index.php?listOrdersNoDelivery" class="button">Listar vendas sem entregas</a>

    <form action="index.php?searchOrder" method="post">
        Procurar <input id="name" name="name" placeholder="nome da pessoa" type="search" /> que seja:
        <label><input name="type" type="radio" value="is_seller" />Vendedor</label>
        <label><input name="type" type="radio" value="is_buyer" />Comprador</label>
        <label><input name="type" type="radio" value="is_gifted" />Presenteado</label>
        <input type="submit" value="Buscar" />
    </form>
    <ul class="table-orders">
        <li class="sticky">
            <label class="id">Id</label>
            <label class="name">Vendedor</label>
            <label class="name">Comprador</label>
            <label class="name">Presenteado</label>
            <label class="name">Entregador</label>
            <label class="full-address">Endereço</label>
            <label class="notes">Etiqueta</label>
            <label class="card">Cartão</label>
            <label class="paid">Pago</label>
            <label class="notes">Notas</label>
        </li>

        <?php
        $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, orders.delivery_at, orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to, u1.name as 'seller_name', u2.name as 'buyer_name', u3.name as 'gifted_name', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, orders.notes, d1.id_sender, u4.name as 'sender_name'
            FROM orders
            INNER JOIN users as u1 ON orders.id_seller = u1.id
            INNER JOIN users as u2 ON orders.id_buyer = u2.id
            INNER JOIN users as u3 ON orders.id_receiver = u3.id
            LEFT JOIN deliveries as d1 ON orders.id = d1.id_order
            LEFT JOIN users as u4 ON d1.id_sender = u4.id
            WHERE orders.no_delivery != 1
            ORDER BY orders.created_at DESC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
        ?>

        <li id="orders-<?php echo $row['id']; ?>">
            <label class="id"><?php echo $row['id']; ?></label>
            <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_seller']; ?>" data-type="users"><?php echo $row['seller_name']; ?></a></label>
            <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_buyer']; ?>" data-type="users"><?php echo $row['buyer_name']; ?></a></label>
            <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_receiver']; ?>" data-type="users"><?php echo $row['gifted_name']; ?></a></label>
            <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_sender']; ?>" data-type="users"><?php echo $row['sender_name']; ?></a></label>
            <label class="full-address">
                <?php if($row['gifted_name'] != 'Sem entrega'): ?>
                <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                <?php echo $row['district']; ?> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?>
                <?php endif; ?>
            </label>
            <label class="notes"><?php echo "<strong>De</strong>: {$row['card_from']}<br /><strong>Para</strong>: {$row['card_to']}"; ?></label>
            <label class="card"><?php if($row['card']): ?><img src="img/icon-card.png" alt="@" /><?php endif; ?></label>
            <label class="paid"><input class="is-paid" <?php if($row['is_paid']) echo 'checked="checked"'; ?> data-id="<?php echo $row['id']; ?>" type="checkbox"></label>
            <label class="notes"><?php
            if($row['no_delivery']):
                echo 'Sem entrega';
            endif; ?>
                <p><?php echo $row['notes']; ?></p>
            </label>

            <label class="actions">
                <a href="javascript:;" class="doShow" data-id="<?php echo $row['id']; ?>" data-type="orders"><img src="img/update.png" alt="" /> Atualizar</a>
                <a href="javascript:;" class="doDelete" data-id="<?php echo $row['id']; ?>" data-type="orders"><img src="img/remove.png" alt="" /> Deletar</a>
            </label>
        </li>
    <?php endforeach; ?>
    </ul>
<br /><br />
    <h2>Estoque</h2>
    <?php $this->countSupply(); ?>
    </div>
<?php
    }





    # Shows all orders with no delivery
    public function listOrdersNoDelivery(){
        $query = $this->db->query("SELECT COUNT(1) FROM orders WHERE no_delivery = 1");
        $count_orders = $query->fetchColumn();
        ?>
    <div id="list">
        <h1><img src="img/orders.png" alt="Ver vendas" title="Ver vendas" /> Listando todas vendas sem entregas (<?php echo $count_orders; ?>)</h1>

    <?php if(isset($_GET['error'])) echo '<p class="error">Nenhum usuário encontrado</p>'; ?>

    <a href="index.php?listOrders" class="button">Listando vendas com entregas</a>

    <ul class="table-orders no-delivery">
        <li class="sticky">
            <label class="id">Id</label>
            <label class="name">Vendedor</label>
            <label class="name">Comprador</label>
            <label class="name">Presenteado</label>
            <label class="address">Endereço</label>
            <label class="notes">Etiqueta</label>
            <label class="card">Cartão</label>
            <label class="paid">Pago</label>
            <label class="notes">Notas</label>
            <label class="card">Retirou</label>
            <label class="notes">Notas 2</label>
        </li>

        <?php
        $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, orders.delivery_at, orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to, u1.name as 'seller_name', u2.name as 'buyer_name', u3.name as 'gifted_name', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, orders.notes, d1.id_sender, u4.name as 'sender_name', orders.collected, orders.notes2
            FROM orders
            INNER JOIN users as u1 ON orders.id_seller = u1.id
            INNER JOIN users as u2 ON orders.id_buyer = u2.id
            INNER JOIN users as u3 ON orders.id_receiver = u3.id
            LEFT JOIN deliveries as d1 ON orders.id = d1.id_order
            LEFT JOIN users as u4 ON d1.id_sender = u4.id
            WHERE orders.no_delivery = 1
            ORDER BY u2.name ASC, u1.name ASC, u3.name ASC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
        ?>

        <li id="orders-<?php echo $row['id']; ?>">
            <label class="id"><?php echo $row['id']; ?></label>
            <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_seller']; ?>" data-type="users"><?php echo $row['seller_name']; ?></a></label>
            <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_buyer']; ?>" data-type="users"><?php echo $row['buyer_name']; ?></a></label>
            <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_receiver']; ?>" data-type="users"><?php echo $row['gifted_name']; ?></a></label>
            <label class="address">
                <?php if($row['gifted_name'] != 'Sem entrega'): ?>
                <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                <?php echo $row['district']; ?> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?>
                <?php endif; ?>
            </label>
            <label class="notes"><?php echo "<strong>De</strong>: {$row['card_from']}<br /><strong>Para</strong>: {$row['card_to']}"; ?></label>
            <label class="card"><?php if($row['card']): ?><img src="img/icon-card.png" alt="@" /><?php endif; ?></label>
<!-- 			<label class="paid"><?php if($row['is_paid']) echo '<img src="img/paid.png" alt="Pago" title="Pago" /> '; ?></label> -->
            <label class="paid"><input class="is-paid" <?php if($row['is_paid']) echo 'checked="checked"'; ?> data-id="<?php echo $row['id']; ?>" type="checkbox"></label>
            <label class="notes"><?php echo $row['notes']; ?></label>
            <label class="paid"><input class="collected" <?php if($row['collected']) echo 'checked="checked"'; ?> data-id="<?php echo $row['id']; ?>" data-type="orders" type="checkbox"></label>
            <label class="notes"><?php echo $row['notes2']; ?></label>

            <label class="actions">
                <a href="javascript:;" class="doShow" data-id="<?php echo $row['id']; ?>" data-type="orders"><img src="img/update.png" alt="" /> Atualizar</a>
                <a href="javascript:;" class="doDelete" data-id="<?php echo $row['id']; ?>" data-type="orders"><img src="img/remove.png" alt="" /> Deletar</a>
            </label>
        </li>
    <?php endforeach; ?>
    </ul>
    </div>
<?php
    }




    # Shows all products
    public function listProblems(){
    ?>

        <h1>Listando os problemas na entrega</h1>

        <ul>
        <?php
        $query = $this->db->query("SELECT * FROM problems ORDER BY created_at ASC");
        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
        ?>
            <li id="problems-<?php echo $row['id_order']; ?>">
                <label class="id"><?php echo $row['id_order']; ?></label>
                <label class="description"><?php echo $row['problem']; ?></label>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php
    }





    # Shows all products
    public function listProducts(){
    ?>
        <div id="list">
        <h1><img src="img/products.png" alt="Ver produtos" title="Ver produtos" /> Listando todos produtos</h1>
        <a href="index.php?cleanTables" class="button finish">Limpar todos dados de venda dessa edição</a>
        <ul>
            <li>
                <label class="id">Id</label>
                <label class="name">Nome</label>
                <label class="price">Preço</label>
                <label class="supply">Estoque</label>
            </li>
        <?php
        $query = $this->db->query("SELECT * FROM products ORDER BY name DESC");
        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
        ?>
            <li id="products-<?php echo $row['id']; ?>">
                <label class="id"><?php echo $row['id']; ?></label>
                <label class="name"><?php echo $row['name']; ?></label>
                <label class="price">R$ <?php echo number_format($row['price'],2,',','.'); ?></label>
                <label class="supply"><?php echo $row['supply']; ?></label>

                <a href="javascript:;" class="doShowProduct" data-id="<?php echo $row['id']; ?>"><img src="img/update.png" alt="" /> Atualizar</a>
                <a href="javascript:;" class="doDelete" data-id="<?php echo $row['id']; ?>" data-type="products"><img src="img/remove.png" alt="" /> Deletar</a>
            </li>
        <?php endforeach; ?>
        </ul>
        </div>
    <?php
    }





    # Login
    public function login(){
        $data = array(
            ':name' => $_POST['name'],
            ':password' => $_POST['password']
            );
        $query = $this->db
            ->prepare("SELECT id, name, is_seller, is_buyer, is_sender, is_gifted, is_admin
                FROM users
                WHERE name = :name AND password = MD5(:password)");

        if($query->execute($data)):
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if(!empty($row)):
                $_SESSION['id'] = $row['id'];
                $_SESSION['name'] = $row['name'];
                if($row['is_buyer'] == 1 || $row['is_sender'] == 1 || $row['is_gifted'] == 1): $_SESSION['type'] = 'user'; endif;
                if($row['is_seller'] == 1): $_SESSION['type'] = 'seller'; endif;
                if($row['is_admin'] == 1): $_SESSION['type'] = 'admin'; endif;
                if($row['is_sender'] == 1): $_SESSION['is_sender'] = 'yes'; endif;
            else:
                header('location: index.php?error=login');
            endif;

        endif;
    }





    # Logout
    public function logout(){
        unset($_SESSION['id']);
        unset($_SESSION['is_sender']);
        unset($_SESSION['name']);
        unset($_SESSION['type']);
    }





    # Logout
    public function map(){
        $query = $this->db
            ->query("SELECT *
                FROM users
                WHERE id = {$_GET['map']}");
        $row = $query->fetch(PDO::FETCH_ASSOC);
    ?>
<!DOCTYPE html>
<html id="map">
<head>
    <meta charset="utf-8">
    <title>Mapa da localidade - Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700" rel="stylesheet" type="text/css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkK3zL6elimsbHUpGJf4eZn47QoKcRyaQ&sensor=true&language=pt-BR"></script>
<script type="text/javascript">
var geocoder; var map;
function initialize() {
    geocoder = new google.maps.Geocoder();
    var address = new google.maps.LatLng(<?php echo $row['geolat']; ?>, <?php echo $row['geolng']; ?>);
    var mapOptions = {
        zoom: 16,
        center: address,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
    var marker = new google.maps.Marker({ position: address, map: map });
}
google.maps.event.addDomListener(window, 'load', initialize);
</script>
</head>
<body>
<div id="list">
<h2>Mapa</h2>
    <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
    <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
    <?php echo $row['district']; ?> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?><br />
    <div id="map-canvas" style="display: block; float: none; height: 420px; margin: 0 auto; width: 700px;"></div>
</div>
</body>
</html>
    <?php
    die();
    }





    # Prepares the div for adding new user/order
    public function newBox(){
        if($_GET['newbox'] == 'keySenders'): ?>
            <h2><img src="img/add-key-sender.png" alt="Adicionar entregador-chave" title="Adicionar entregador-chave" /> Adicionar entregador-chave</h2>
            <form action="index.php?verifyKeySender" method="post">
                <input class="autocomplete-key-sender" id="name" name="name" placeholder="Nome" type="text" required /><br />
                <input type="submit" value="Adicionar entregador-chave" />
            </form>



        <?php elseif($_GET['newbox'] == 'noteProblem'):
                $query = $this->db->query("SELECT note
                    FROM problems
                    WHERE id = {$_GET['id']}");
                $row = $query->fetch(); ?>

            <h2>Editar nota do problema na entrega</h2>
            <form action="index.php?noteProblem=<?php echo $_GET['id'] ?>" method="post">
                <textarea id="note" name="note" placeholder="Qual a providência tomada?"><?php echo $row['note']; ?></textarea><br />
                <input name="id_problem" type="hidden" value="<?php echo $_GET['id'] ?>" />
                <input type="submit" value="Salvar nota" />
            </form>



        <?php elseif($_GET['newbox'] == 'order'): ?>
            <h2><img src="img/add-order.png" alt="Adicionar venda" title="Adicionar venda" /> Adicionar nova venda</h2>

            <form action="index.php?verifyBuyer" id="" method="post">
                <input class="autocomplete-name" id="buyer" name="buyer" placeholder="Nome do comprador" type="text" required /> <a href="javascript:$('#box-new-buyer').fadeIn();" class="new-buyer">Adicionar comprador</a><br />

                <input class="autocomplete-name" id="gifted" name="gifted" placeholder="Nome do presenteado" type="text" required /> <a href="javascript:;" class="new-gifted">Adicionar presenteado</a><br />
                <input type="submit" value="Validar venda" />
            </form>
            <div id="box-new-buyer">
            <h2>Adicionar novo comprador</h2>
            <div id="map-canvas"></div>
            <form action="index.php?addUser=1&return=buyer" id="form-add-buyer" method="post">
                <input id="name" name="name" placeholder="Nome" required type="text" />
                <input id="email" name="email" placeholder="Email" type="email" />
                <input id="password" name="password" placeholder="Senha" type="hidden" value="" /><br />
                <input class="mobile" id="mobile" name="mobile" placeholder="Telefone principal" required type="text" />
                <input class="validate-phone" id="phone" name="phone" placeholder="Outro telefone" type="text" /><br>
                <input class="gifted-address" id="address" name="address" placeholder="Endereço" type="text" />
                <input cla ss="gifted-address" id="address2" name="address2" placeholder="Complemento" type="text" />
                <input id="reference" name="reference" placeholder="Referência" type="text" /><br />
                <input class="district gifted-address" id="district" name="district" placeholder="Bairro" type="hidden" />
<!--				<input class="gifted-address" id="city" name="city" placeholder="Cidade" type="text" />-->
                <input class="gifted-address" id="city" name="city" type="hidden">
<!--
                    <option disabled="disabled" selected="selected">Cidade</option>
                    <option>Belo Horizonte</option>
                    <option>Betim</option>
                    <option>Contagem</option>
                    <option>Lagoa Santa</option>
                    <option>Nova Lima</option>
                    <option>Ribeirão das Neves</option>
                    <option>Sabará</option>
                    <option>Santa Luzia</option>
                </select>
-->
                <input class="gifted-address" id="province" name="province" placeholder="UF" type="hidden" value="MG" /><br />
                <input id="notes" name="notes" placeholder="Notas" type="text" />
                <input id="geolat" name="geolat" type="hidden" />
                <input id="geolng" name="geolng" type="hidden" />
                <input name="is_buyer" type="hidden" value="on" /><br />

                <input type="submit" value="Salvar comprador" />
            </form>
            </div>



        <?php elseif($_GET['newbox'] == 'product'): ?>

            <h2><img src="img/add-product.png" alt="Adicionar produto" title="Adicionar produto" /> Adicionar produto</h2>
            <form action="index.php?addProduct=1" method="post">
                <input id="name" name="name" placeholder="Nome" type="text" required /><br />
                <input id="price" name="price" placeholder="Preço" type="text" required /><br />
                <input id="supply" name="supply" placeholder="Estoque inicial" type="text" required /><br />
                <input type="submit" value="Continuar" />
            </form>



        <?php elseif($_GET['newbox'] == 'reportProblem'): ?>

            <h2>Relatar problema na entrega</h2>
            <form action="index.php?reportProblem=<?php echo $_GET['id'] ?>" method="post">
                <textarea name="problem" placeholder="Qual o problema encontrado?"></textarea><br />
                <input name="id_order" type="hidden" value="<?php echo $_GET['id'] ?>" />
                <input type="submit" value="Relatar problema" />
            </form>



        <?php elseif($_GET['newbox'] == 'user'): ?>

            <h2><img src="img/add-user.png" alt="Adicionar usuário" title="Adicionar usuário" /> Adicionar novo usuário ou alterar sua função</h2>
            <form action="index.php?verifyUser" method="post">
                <input class="autocomplete-name" id="name" name="name" placeholder="Nome" type="text" required /><br />
                <input type="submit" value="Continuar" />
            </form>

    <?php endif;
        die();
    }





    # Add a note to a problem with delivery
    public function noteProblem(){
        $data = array(
            ':id_problem' => $_POST['id_problem'],
            ':note' => $_POST['note']
            );

        @$this->db
            ->prepare("UPDATE problems SET note = :note WHERE id = :id_problem")
            ->execute($data);
        header('location: index.php?allDeliveries=problems');
        die();
    }





    # Formats each page of delivery for the sender
    public function printDeliveries(){

        $query = $this->db
            ->query("SELECT name FROM users WHERE id = {$_GET['printDeliveries']}");
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $sender = $row['name'];
        $query = $this->db
            ->query("SELECT COUNT(u3.district)
                FROM orders
                INNER JOIN users as u3 ON orders.id_receiver = u3.id
                INNER JOIN deliveries ON deliveries.id_order = orders.id
                INNER JOIN users ON deliveries.id_sender = users.id
                WHERE orders.no_delivery != 1 AND users.id = {$_GET['printDeliveries']}
                GROUP BY u3.district");
        $count_pages = count($query->fetchAll(PDO::FETCH_ASSOC));

        $query = $this->db
            ->query("SELECT orders.is_paid, orders.card, orders.card_from, orders.card_to, /* orders.delivery_at, */ p1.name as 'product_name',
                u1.name as 'seller_name', u1.phone as 'seller_phone', u1.mobile as 'seller_mobile',
                u2.name as 'buyer_name', u2.phone as 'buyer_phone', u2.mobile as 'buyer_mobile',
                u3.id as 'gifted_id', u3.name as 'gifted_name', u3.phone as 'gifted_phone', u3.mobile as 'gifted_mobile', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, u3.notes, orders.notes as notesOrders
                FROM orders
                INNER JOIN users as u1 ON orders.id_seller = u1.id
                INNER JOIN users as u2 ON orders.id_buyer = u2.id
                INNER JOIN users as u3 ON orders.id_receiver = u3.id
                INNER JOIN products as p1 ON orders.id_product = p1.id
                INNER JOIN deliveries ON deliveries.id_order = orders.id
                INNER JOIN users ON deliveries.id_sender = users.id
                WHERE orders.no_delivery != 1 AND users.id = {$_GET['printDeliveries']}
                ORDER BY u3.district ASC, users.id DESC");
        $rs = $query->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($rs)):
            $last_district = '';
            $i = 0; ?>
<!DOCTYPE html>
<html id="print" lang="">
<head>
    <meta charset="utf-8">
    <title>Ficha do Entregador - Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css" />
    <link href="http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700" rel="stylesheet" type="text/css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkK3zL6elimsbHUpGJf4eZn47QoKcRyaQ&sensor=true&language=pt-BR"></script>
</head>


<body>
    <h1>Ficha de entrega <span><?php echo $sender; ?></span></h1>
    <h4>Instruções para entrega das cestas</h4>
    <pre><?php include('aviso.txt'); ?></pre><br><br>
    <ol>
<?php

    $district_number = 0;
    foreach($rs as $row):?>
    <li>
        <div class="buying-info">
            <p>
                <strong>Vendedor:</strong> <?php echo $row['seller_name']; ?> -
                <?php echo $row['seller_mobile']; ?><?php if(!empty($row['seller_phone']) && !empty($row['seller_mobile'])) echo ' / '; ?><?php echo $row['seller_phone']; ?>
            </p>

            <p>
                <strong>Comprador:</strong> <?php echo $row['buyer_name']; ?> -
                <?php echo $row['buyer_mobile']; ?><?php if(!empty($row['buyer_phone']) && !empty($row['buyer_mobile'])) echo ' / '; ?><?php echo $row['buyer_phone']; ?>
            </p>

            <div class="from-to">
                <p>De: <?php echo substr($row['card_from'],0,50); ?><br />
                Para: <?php echo substr($row['card_to'],0,50); ?></p>
            </div>

        </div>
        <div class="delivery-info">
            <p>
                <strong>Presenteado:</strong> <?php echo $row['gifted_name']; ?> -
                <?php echo $row['gifted_mobile']; ?><?php if(!empty($row['gifted_phone']) && !empty($row['gifted_mobile'])) echo ' / '; ?><?php echo $row['gifted_phone']; ?>
            </p>
            <address>
                <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                <?php echo $row['district']; ?> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?>
            </address>
            <?php echo ($row['notes']) ? "<p><strong>Obs</strong>: {$row['notes']}</p>" : ''; ?>
            <?php echo ($row['notesOrders']) ? "<p><strong>Obs</strong>: {$row['notesOrders']}</p>" : ''; ?>

            <?php if($row['card']) echo '<p class="print-card"><img src="img/icon-card.png" alt="@" /> Entregar cartão</p>'; ?>
        </div>
    </li>


    <?php if($last_district != $row['district']): ?>
<?php
    endif;
    $last_district = $row['district'];
endforeach;
?>
</ol>



<?php
$query = $this->db
    ->query("SELECT GROUP_CONCAT(users.geolat) AS geolat, GROUP_CONCAT(users.geolng) AS geolng, users.district, districts.geolat AS district_geolat, districts.geolng AS district_geolng
                FROM orders
                INNER JOIN users ON orders.id_receiver = users.id
                INNER JOIN deliveries ON deliveries.id_order = orders.id
                LEFT JOIN districts ON districts.district = users.district
                WHERE orders.no_delivery != 1 AND deliveries.id_sender = {$_GET['printDeliveries']}
                GROUP BY users.district
                ORDER BY users.district ASC, deliveries.id ASC");
    $rs = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach($rs as $row):
        $geolat[] = (explode(',',$row['geolat']));
        $geolng[] = (explode(',',$row['geolng']));
    endforeach;

    $count_maps = count($geolat);
    $i = 0;
?>



<script type="text/javascript">
var geocoder; var map;
function initialize() {
    geocoder = new google.maps.Geocoder();



    <?php for($maps = 0; $maps < $count_maps; $maps++): ?>

        var mapOptions = {
            center: new google.maps.LatLng(<?php echo (!empty($rs[$maps]['district_geolat']) ? $rs[$maps]['district_geolat'] : '-19.9183'); ?>, <?php echo (!empty($rs[$maps]['district_geolng']) ? $rs[$maps]['district_geolng'] : '-43.9401'); ?>),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            zoom: 16
        }
        map = new google.maps.Map(document.getElementById('map-canvas-<?php echo $maps; ?>'), mapOptions);

        <?php for($m = 0; $m < count($geolat[$maps]); $m++): ?>
        new google.maps.Marker({ position: new google.maps.LatLng(<?php echo $geolat[$maps][$m]; ?>, <?php echo $geolng[$maps][$m] ?>), map: map });
        <?php endfor; ?>

    <?php endfor; ?>



}
google.maps.event.addDomListener(window, 'load', initialize);
</script>
</body>
</html>
    <?php
        else:
            echo 'Ocorreu algum erro. Por favor contate o webmaster.';
        endif;
        die();
    }






    # Print the orders report
    public function printOrders(){
$now = date("d/m/Y \à\s H:i");
$html = <<<EOL
<!DOCTYPE html>
<html id="print" lang="">
<head>
    <meta charset="utf-8">
    <title>Relatório de Vendas - Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { color: #434747; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 18px; font-weight: 300; line-height: 1.4; }
h1 { color: #1f1f1c; font-size: 28px; margin-bottom: 1em; }
h1 span { font-size: 18px; }
ul { list-style: none; }
thead { display: table-header-group; }
.card, .email, .id, .paid, .total { width: 40px; }
.city, .name, .phone, .time { width: 130px; }
.description, .full-data { width: 350px; }
.header td { font-size: 16px; font-weight: bold; }
.notes strong { color: black; font-weight: normal; text-decoration: underline; }
</style>
</head>
<body>
    <h1>Relatório de Vendas <span>(Emitido dia $now)</span></h1>
    <table border="1" bordercolor="black" cellpadding="10" cellspacing="0">
    <thead>
        <tr class="header">
            <th class="id">Id</th>
            <th class="name">Vendedor</th>
            <th class="name">Comprador</th>
            <th class="name">Presenteado</th>
            <th class="full-address">Endereço</th>
            <th class="notes">Etiqueta</th>
            <th class="card">Cartão</th>
            <th class="paid">Pago</th>
            <th class="notes">Notas</th>
        </tr>
    </thead>
    <tbody>
EOL;

        $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, /* orders.delivery_at, */ orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to, u1.name as 'seller_name', u2.name as 'buyer_name', u3.name as 'gifted_name', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, orders.notes
            FROM orders
            INNER JOIN users as u1 ON orders.id_seller = u1.id
            INNER JOIN users as u2 ON orders.id_buyer = u2.id
            INNER JOIN users as u3 ON orders.id_receiver = u3.id
            ORDER BY orders.created_at DESC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            $card_html = ''; $is_paid_html = ''; $delivery_html = '';
            $reference = ($row['reference']) ? "({$row['reference']})" : '';
            $city = ($row['city']) ? "- {$row['city']} / {$row['province']}" : '';
            $card = ($row['card_from'] || $row['card_to']) ? "<address><small><strong>De</strong>: {$row['card_from']}<br /><strong>Para</strong>: {$row['card_to']}</small></address>" : '';
            if($row['card']) $card_html = 'Sim';
            if($row['is_paid']) $is_paid_html = 'Sim';
            if($row['no_delivery']):
                $delivery_html = 'Sem entrega';
/*
            else:
                if($row['delivery_at'] != '00:00:00') $delivery_html = 'Entregar às ' . substr($row['delivery_at'],0,-3);
*/
            endif;
            $notes = ($row['notes']) ? "<p><strong>Obs</strong>: {$row['notes']}</p>" : '';


$html .= <<<EOL
        <tr>
            <td class="id">{$row['id']}</td>
            <td class="name">{$row['seller_name']}</td>
            <td class="name">{$row['buyer_name']}</td>
            <td class="name"><strong>{$row['gifted_name']}</strong></td>
            <td class="medium-address">
                {$row['address']} {$row['address2']} $reference<br />
                <strong>{$row['district']}</strong> $city
            </td>
            <td class="notes">$card</td>
            <td class="card">$card_html</td>
            <td class="paid">$is_paid_html</td>
            <td class="notes">$delivery_html
                $notes
            </td>
        </tr>
EOL;

    endforeach;
$html .= <<<EOL
    </tbody>
    </table>
EOL;

    echo $html;
    die();
    }





    # Print the report of the problems in deliveries
    public function problemsReport(){
        $now = date("d/m/Y \à\s H:i");
        $id_seller = (isset($_GET['sellerReportPrint']) && $_SESSION['type'] == 'admin') ? $_GET['sellerReportPrint'] : $_SESSION['id'];
        $query = $this->db->query("SELECT COUNT(1)
            FROM problems");
        $count_problems = $query->fetchColumn();


$html = <<<EOL
<!DOCTYPE html>
<html id="print" lang="">
<head>
    <meta charset="utf-8">
    <title>Relatório de Entregas com Problema - Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { color: #434747; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 18px; font-weight: 300; line-height: 1.4; }
h1 { color: #1f1f1c; font-size: 28px; margin-bottom: 1em; }
h1 span { font-size: 18px; }
ul { list-style: none; }
thead { display: table-header-group; }
.card, .email, .id, .paid, .total { width: 40px; }
.city, .name, .phone, .time { width: 130px; }
.description, .full-data { width: 350px; }
.header td { font-size: 16px; font-weight: bold; }
</style>
</head>
<body>
    <h1>Relatório de Entregas com Problema ($count_problems) <span>(Emitido dia $now)</span></h1>
    <table border="1" bordercolor="black" cellpadding="10" cellspacing="0">
    <thead>
        <tr class="header">
            <th class="id">Id</th>
            <th class="name">Entregador</th>
            <th class="name">Vendedor</th>
            <th class="name">Comprador</th>
            <th class="name">Presenteado</th>
            <th class="full-address">Endereço</th>
            <th class="card">Cartão</th>
            <th class="notes">Notas</th>
        </tr>
    </thead>
    <tbody>
EOL;

        $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, orders.delivery_at, orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to,
            u1.name as 'seller_name', u1.phone as 'seller_phone', u1.mobile as 'seller_mobile',
            u2.name as 'buyer_name', u2.phone as 'buyer_phone', u2.mobile as 'buyer_mobile',
            u3.id as 'gifted_id', u3.name as 'gifted_name', u3.phone, u3.mobile, u3.district as 'gifted_district', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province,
            p1.name as 'product_name', problems.id as 'problem_id', problems.problem, problems.note, problems.is_resolved
            FROM orders
            INNER JOIN users as u1 ON orders.id_seller = u1.id
            INNER JOIN users as u2 ON orders.id_buyer = u2.id
            INNER JOIN users as u3 ON orders.id_receiver = u3.id
            INNER JOIN products as p1 ON orders.id_product = p1.id
            INNER JOIN problems ON problems.id_order = orders.id
            ORDER BY problems.is_resolved ASC, orders.created_at DESC");

        $deliveries = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach($deliveries as $row):

            $address = ($row['district'] || $row['city'] || $row['province']) ? "<strong>{$row['district']}</strong> - {$row['city']} / {$row['province']}" : '';
            $card_html = ($row['card']) ? 'Sim' : 'Não';
            $reference = ($row['reference']) ? "({$row['reference']})" : '';

$html .= <<<EOL
        <tr>
            <td class="id">{$row['id']}</td>
            <td class="name">{$row['seller_name']}</td>
            <td class="name">{$row['seller_name']}</td>
            <td class="name">{$row['buyer_name']}</td>
            <td class="name"><strong>{$row['gifted_name']}</strong></td>
            <td class="full-address">
                {$row['address']} {$row['address2']} $reference<br />
                $address
            </td>
            <td class="card">$card_html</td>
            <td class="notes">
                <p>{$row['note']}</p>
            </td>
        </tr>
EOL;

    endforeach;
$html .= <<<EOL
    </tbody>
    </table>
EOL;

    echo $html;
    die();
    }




    # Report problem with delivery
    public function redistributeDeliveries(){
    ?>
        <div id="list">
            <div id="columns">
                <h1>Redistribuir entregas</h1>

                <div class="box-deliveries">
                    <h2>Vendas</h2>
                    <ul>
                        <li>
                            <label class="name">Nome</label>
                            <label class="address">Endereço</label>
                            <label class="district">Bairro</label>
                        </li>

                        <?php
                        $query = $this->db
                            ->query("SELECT orders.id, users.name, users.address, users.district, users.city
                                FROM orders
                                JOIN users ON orders.id_receiver = users.id
                                WHERE orders.has_sender IS NULL AND users.name != 'Sem entrega'
                                ORDER BY users.district");
                        $rs = $query->fetchAll(PDO::FETCH_ASSOC);

                        foreach($rs as $row): ?>
                        <li class="column" draggable="true" id="<?php echo $row['id']; ?>">
                            <label class="name"><?php echo $row['name']; ?></label>
                            <label class="address"><?php echo $row['address']; ?></label>
                            <label class="district"><?php echo $row['district']; ?><br><small><?php echo $row['city']; ?></small></label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>



                <div class="box-senders">
                    <h2>Entregadores</h2>
                    <ul>
                        <li>
                            <label class="name">Nome</label>
                            <label class="address">Endereço</label>
                            <label class="district">Bairro</label>
                        </li>
                        <?php
                        $query = $this->db
                            ->query("SELECT id, name, address, district, city
                                FROM users
                                WHERE is_sender = 1 AND is_active = 1
                                ORDER BY name ASC");
                        $rs = $query->fetchAll(PDO::FETCH_ASSOC);

                        foreach($rs as $row): ?>
                        <li class="column" id="<?php echo $row['id']; ?>">
                            <label class="name"><?php echo $row['name']; ?></label>
                            <label class="address"><?php echo $row['address']; ?></label>
                            <label class="district"><?php echo $row['district']; ?><br><small><?php echo $row['city']; ?></small></label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="clear"></div>
            </div>
        </div>



<script>
var dragSrcEl = null;
function handleDragStart(e) {
    e.dataTransfer.setData('text', this.id);

    $(e.currentTarget).addClass('hover-fixed');
    dragSrcEl = this;
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    return false;
}
function handleDragEnter(e) { $(this).addClass('hover'); }
function handleDragLeave(e) { $(this).removeClass('hover'); }
function handleDrop(e) {
    e.stopPropagation();
    e.preventDefault();
    if ($(this).parent().parent().attr('class') == 'box-senders') {
        var selected_ids = [];

        $('.selected').each(function(i){
            selected_ids.push($(this).attr('id'));
        });

        if(selected_ids.length > 0)
            order_ids = selected_ids.join();
        else {
            order_ids = e.dataTransfer.getData('Text', this.id);
        }

        $.get('index.php?addDelivery', {
            order: order_ids,
            sender: this.id
        }, function(data) {
            if(selected_ids.length > 0) {
                $('.selected').fadeOut('slow', function(){
                    $('.selected').remove();
                });
            } else {
                $(dragSrcEl).fadeOut('slow', function(){
                    $(dragSrcEl).remove();
                });
            }
        });
    }
    return false;
}
function handleDragEnd(e) {
    [].forEach.call(cols, function (col) { col.classList.remove('hover'); });
    $(e.currentTarget).removeClass('hover-fixed');
}

var cols = document.querySelectorAll('.column');
[].forEach.call(cols, function(col) {
    col.addEventListener('dragstart', handleDragStart, false);
    col.addEventListener('dragenter', handleDragEnter, false)
    col.addEventListener('dragover', handleDragOver, false);
    col.addEventListener('dragleave', handleDragLeave, false);
    col.addEventListener('drop', handleDrop, false);
    col.addEventListener('dragend', handleDragEnd, false);
});
</script>
    <?php
    }




    # Report problem with delivery
    public function reportProblem(){
        $data = array(
            ':id_order' => $_POST['id_order'],
            ':problem' => $_POST['problem']
            );

        @$this->db
            ->prepare("INSERT INTO problems (id_order, problem)
                VALUES (:id_order, :problem)")
            ->execute($data);
        header('location: ' . $_SERVER['HTTP_REFERER']);
        die();
    }





    # Removes an order from a sender
    public function removeDelivery(){
        @$this->db
            ->prepare("DELETE FROM deliveries WHERE id_order = {$_GET['removeDelivery']}")
            ->execute();
        @$this->db
            ->prepare("UPDATE orders SET has_sender = NULL WHERE id = {$_GET['removeDelivery']}")
            ->execute();
        die();
    }





    # Exports a XLS of all deliveries
    public function reportDeliveries(){

        // Original PHP code by Chirp Internet: www.chirp.com.au
        // Please acknowledge use of this code by including this header

        function cleanData(&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        }


        $query = $this->db->query("SELECT sender.name as sender_name, sender.address as sender_address, sender.district as sender_district, sender.city as sender_city, orders.id as order_id, gifted.name as gifted_name, gifted.address as gifted_address, gifted.district as gifted_district, gifted.city as gifted_city
            FROM deliveries
            JOIN orders ON deliveries.id_order = orders.id
            JOIN users as gifted ON gifted.id = orders.id_receiver
            JOIN users as sender ON sender.id = deliveries.id_sender
            WHERE orders.no_delivery != 1");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            # Generates each row of the sheet
            $exportXLS[] = array("{$row['sender_name']}","{$row['sender_address']}","{$row['sender_district']}","{$row['sender_city']}","{$row['order_id']},{$row['gifted_name']}","{$row['gifted_address']}","{$row['gifted_district']}","{$row['gifted_city']}");
        endforeach;



        # Print the data on Excel sheet
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition: attachment; filename=relatorio-entregas-entregadores.xls');
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');

        $header_titles = array('Nome do entregador','Endereço do entregador','Bairro do entregador','Cidade do entregador','ID da venda','Nome do presenteado','Endereço do presenteado','Bairro do presenteado','Cidade do presenteado');
        echo implode("\t", $header_titles) . "\n";

        foreach($exportXLS as $row) {
            array_walk($row, 'cleanData');
            echo implode("\t", array_values($row)) . "\n";
        }
        die();

    die();
    }




    # Resets seller password to 'cicx123'
    public function resetPassword(){
        @$this->db
            ->prepare("UPDATE users SET password = MD5('cicx123') WHERE id = {$_GET['resetPassword']}")
            ->execute();
        die();
    }



    # Reset printed sheets checkboxes to empty
    public function resetIsConfirmed(){
        @$this->db
            ->prepare("UPDATE users SET is_confirmed = 0")
            ->execute();
        die();
    }



    # Reset printed sheets checkboxes to empty
    public function resetSheetPrinted(){
        @$this->db
            ->prepare("UPDATE users SET sheet_printed = 0")
            ->execute();
        die();
    }





    # Resets seller password to 'cicx123'
    public function resetPasswordEmail(){
        @$this->db
            ->prepare("UPDATE users SET password = MD5('cicx123') WHERE id = {$_GET['resetPasswordEmail']}")
            ->execute();
        header('location: index.php?reset=1');
    }






    # Reset the delivery clock if the sender mistakenly started the clock
    public function resetTime(){
        @$this->db
            ->prepare("UPDATE senders SET date_beginning = '', date_finish = '' WHERE id = {$_GET['resetTime']}")
            ->execute();
        header('location: index.php?adminSenders');
    }






    # Search for specific user(s)
    public function search(){
        $data = array(
            ':name' => '%'. $_POST['search'] . '%'
        );
        $query_sender = '';
        if($_GET['type'] == 'is_sender') $query_sender = ', (SELECT count(1) FROM deliveries WHERE deliveries.id_sender = users.id) as count_deliveries';
        $query = $this->db
            ->prepare("SELECT * $query_sender
                FROM users
                WHERE name LIKE :name AND {$_GET['type']} = 1");
        $query->execute($data);
        $rs = $query->fetchAll(PDO::FETCH_ASSOC);

        if($rs):
            if(isset($_GET['type'])) $group = $_GET['type'];
            switch ($group):
                case 'is_seller': $user_type_label = "vendedores"; break;
                case 'is_buyer': $user_type_label = "compradores"; break;
                case 'is_sender': $user_type_label = "entregadores"; break;
                case 'is_gifted': $user_type_label = "presenteados"; break;
            endswitch;

            $redirect = (!empty($_GET['listAll'])) ? $_GET['listAll'] : $_GET['type'];
            ?>
            <div id="list">
            <h1><img src="img/results.png" alt="Resultados da busca" title="Resultados da busca" /> Resultados da busca de <?php echo $user_type_label; ?> com nome "<?php echo $_POST['search']; ?>"</h1>

            <form action="index.php?type=<?php echo $redirect; ?>" method="post">
                <input id="search" name="search" placeholder="Buscar <?php echo $user_type_label; ?>" type="search" />
                <input type="submit" value="Buscar" />
            </form>

            <ul>
                <li>
                    <label class="id">Id</label>
                    <label class="name">Usuário</label>
                    <label class="email">Email</label>
                    <label class="phone">Telefone</label>
                    <label class="full-address">Endereço</label>
                    <label class="notes">Notas</label>
                </li>
            <?php foreach($rs as $row): ?>

                <li id="users-<?php echo $row['id']; ?>">
                    <label class="id"><?php echo $row['id']; ?></label>
                    <label class="name"><?php echo $row['name']; ?></label>
                    <label class="email tooltip"><?php if(!empty($row['email'])): ?><img src="img/email.png" alt="" />
                        <address><?php echo $row['email']; ?></address><?php endif; ?>
                    </label>
                    <label class="phone"><?php echo ($row['mobile'] == '(00) 0000-0000') ? 'Usar telefone do comprador' : $row['mobile']; ?><?php if(!empty($row['phone']) && !empty($row['mobile'])) echo ' / '; ?><?php echo $row['phone']; ?></label>

                    <label class="full-address tooltip"><?php if(!empty($row['district'])): ?>Ver endereço
                        <address>
                        <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                        <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                        <?php echo $row['district']; ?> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?>
                        </address><?php endif; ?>
                    </label>

                    <label class="notes">
                        <?php if($row['notes']) echo $row['notes']; ?>
                    </label>


                <?php if($_SESSION['type'] != 'user'): ?>
                    <a href="javascript:;" class="doShow" data-id="<?php echo $row['id']; ?>" data-type="users"><img src="img/update.png" alt="" /> Atualizar</a>
                    <a href="javascript:;" class="doDelete" data-id="<?php echo $row['id']; ?>" data-type="users"><img src="img/remove.png" alt="" /> Deletar</a>
                <?php endif; ?>
                <?php if($_SESSION['type'] == 'admin' && isset($row['count_deliveries']) && $row['count_deliveries']): ?><a class="see-deliveries" href="index.php?showDeliveries=<?php echo $row['id']; ?>"><img src="img/see-deliveries.gif" alt="" />Ver entregas (<?php echo $row['count_deliveries']; ?>)</a><?php endif; ?>

                </li>

            <?php endforeach; ?>
            </ul>
            </div>

        <?php
        else:
            $redirect = (!empty($_GET['listAll'])) ? $_GET['listAll'] : $_GET['type'];
            ?>
            <script type="text/javascript">
            window.location.href = 'index.php?error=1&listAll=<?php echo $redirect; ?>'
            </script>
        <?php
        endif;
    }





    # Search for specific user(s) that has orders
    public function searchOrder(){
        $id_field = str_replace("is_", "id_", $_POST['type']);
        if($id_field == 'id_gifted') $id_field = 'id_receiver';
        $data = array(
            ':name' => '%' . $_POST['name'] . '%'
        );
        $query = $this->db
            ->prepare("SELECT orders.id, orders.created_at, orders.no_delivery, /* orders.delivery_at, */ orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to, u1.name as 'seller_name', u2.name as 'buyer_name', u3.name as 'gifted_name', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, orders.notes, d1.id_sender, u4.name as 'sender_name'
            FROM orders
            INNER JOIN users as u1 ON orders.id_seller = u1.id
            INNER JOIN users as u2 ON orders.id_buyer = u2.id
            INNER JOIN users as u3 ON orders.id_receiver = u3.id
            INNER JOIN users ON orders.$id_field = users.id
            LEFT JOIN deliveries as d1 ON orders.id = d1.id_order
            LEFT JOIN users as u4 ON d1.id_sender = u4.id
            WHERE users.name LIKE :name AND users.{$_POST['type']} = 1
            ORDER BY orders.created_at DESC");

        $query->execute($data);

        $rs = $query->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($rs)):
            if(isset($_POST['type'])) $group = $_POST['type'];
            switch ($group):
                case 'is_seller': $user_type_label = "vendedores"; break;
                case 'is_buyer': $user_type_label = "compradores"; break;
                case 'is_sender': $user_type_label = "entregadores"; break;
                case 'is_gifted': $user_type_label = "presenteados"; break;
            endswitch;
            ?>
            <div id="list">
            <h1>Resultados da busca de vendas com <?php echo $user_type_label; ?> de nome "<?php echo $_POST['name']; ?>"</h1>

            <?php if(isset($_GET['error'])) echo '<p class="error">Nenhum usuário encontrado</p>'; ?>
            <form action="index.php?searchOrder" method="post">
                Procurar <input id="name" name="name" placeholder="Nome da pessoa" type="search" /> que seja:
                <label><input name="type" type="radio" value="is_seller" />Vendedor</label>
                <label><input name="type" type="radio" value="is_buyer" />Comprador</label>
                <label><input name="type" type="radio" value="is_gifted" />Presenteado</label>
                <input type="submit" value="Buscar" />
            </form>
            <ul class="table-orders">
                <li>
                    <label class="id">Id</label>
                    <label class="name">Vendedor</label>
                    <label class="name">Comprador</label>
                    <label class="name">Presenteado</label>
                    <label class="name">Entregador</label>
                    <label class="full-address">Endereço</label>
                    <label class="notes">Etiqueta</label>
                    <label class="card">Cartão</label>
                    <label class="paid">Pago</label>
                    <label class="notes">Notas</label>
                </li>

            <?php foreach($rs as $row): ?>
                <li id="orders-<?php echo $row['id']; ?>">
                    <label class="id"><?php echo $row['id']; ?></label>
                    <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_seller']; ?>" data-type="users"><?php echo $row['seller_name']; ?></a></label>
                    <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_buyer']; ?>" data-type="users"><?php echo $row['buyer_name']; ?></a></label>
                    <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_receiver']; ?>" data-type="users"><?php echo $row['gifted_name']; ?></a></label>
                    <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_sender']; ?>" data-type="users"><?php echo $row['sender_name']; ?></a></label>
                    <label class="full-address">
                        <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                        <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?><br />
                        <?php echo $row['district']; ?> - <?php echo $row['city']; ?> / <?php echo $row['province']; ?>
                    </label>
                    <label class="notes"><?php echo "<strong>De</strong>: {$row['card_from']}<br /><strong>Para</strong>: {$row['card_to']}"; ?></label>
                    <label class="card"><?php if($row['card']): ?><img src="img/icon-card.png" alt="@" /><?php endif; ?></label>
                    <label class="paid"><input class="is-paid" <?php if($row['is_paid']) echo 'checked="checked"'; ?> data-id="<?php echo $row['id']; ?>" type="checkbox"></label>
                    <label class="notes"><?php
                    if($row['no_delivery']):
                        echo 'Sem entrega';
/*
                    else:
                        if($row['delivery_at'] != '00:00:00') echo 'Entregar às ' . substr($row['delivery_at'],0,-3);
*/
                    endif; ?>
                        <p><?php echo $row['notes']; ?></p>
                    </label>

                    <label class="actions">
                        <a href="javascript:;" class="doShow" data-id="<?php echo $row['id']; ?>" data-type="orders"><img src="img/update.png" alt="" /> Atualizar</a>
                        <a href="javascript:;" class="doDelete" data-id="<?php echo $row['id']; ?>" data-type="orders"><img src="img/remove.png" alt="" /> Deletar</a>
                    </label>
                </li>

            <?php endforeach; ?>
            </ul>
            </div>
        <?php else: ?>
            <script type="text/javascript">
            window.location.href = 'index.php?error=1&listOrders'
            </script>
        <?php
        endif;
    }





    # Shows every sell the seller has made
    public function sellerReport(){
        $id_seller = (isset($_GET['sellerReport']) && $_SESSION['type'] == 'admin') ? $_GET['sellerReport'] : $_SESSION['id'];
        $query = $this->db->query("SELECT COUNT(1)
            FROM orders
            WHERE id_seller = $id_seller");
        $count_orders = $query->fetchColumn();

        $query = $this->db->query("SELECT name
            FROM users
            WHERE id = $id_seller");
        $user_name = $query->fetchColumn();

        ?>
    <div id="list">
        <h1><img src="img/orders.png" alt="Relatórios de vendas - <?php echo $user_name; ?>" title="Relatórios de vendas - <?php echo $user_name; ?>" /> Relatórios de vendas - <?php echo $user_name; ?> (<?php echo $count_orders; ?>)</h1>
        <a href="index.php?sellerReportPrint<?php if($_SESSION['type'] == 'admin') echo '=' . $_GET['sellerReport'];?>" class="button" target="_blank">Gerar relatório para impressão</a>
        <a href="index.php?exportSellerReport<?php if($_SESSION['type'] == 'admin') echo '=' . $_GET['sellerReport'];?>" class="button" target="_blank">Exportar relatório</a>
        <ul class="table-orders">
            <li>
                <label class="id">Id</label>
                <label class="name">Comprador</label>
                <label class="name">Presenteado</label>
                <label class="name">Entregador</label>
                <label class="medium-address">Endereço</label>
                <label class="notes">Etiqueta</label>
                <label class="card">Cartão</label>
                <label class="paid">Pago</label>
                <label class="short-notes">Notas</label>
            </li>

            <?php
            $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, /* orders.delivery_at, */ orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to, u2.name as 'buyer_name', u3.name as 'gifted_name', d1.id_sender, u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, orders.notes, u4.name as 'sender_name'
                FROM orders
                LEFT JOIN users as u2 ON orders.id_buyer = u2.id
                LEFT JOIN users as u3 ON orders.id_receiver = u3.id
                LEFT JOIN deliveries as d1 ON orders.id = d1.id_order
                LEFT JOIN users as u4 ON d1.id_sender = u4.id
                WHERE id_seller = $id_seller
                ORDER BY u2.name ASC, u3.name ASC");

            foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):

                $address = ($row['district'] || $row['city'] || $row['province']) ? "<strong>{$row['district']}</strong> - {$row['city']} / {$row['province']}" : '';
            ?>

            <li id="orders-<?php echo $row['id']; ?>">
                <label class="id"><?php echo $row['id']; ?></label>
                <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_buyer']; ?>" data-type="users"><?php echo $row['buyer_name']; ?></a></label>
                <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_receiver']; ?>" data-type="users"><?php echo $row['gifted_name']; ?></a></label>
                <label class="name"><a href="javascript:;" class="doShow" data-id="<?php echo $row['id_sender']; ?>" data-type="users"><?php echo $row['sender_name']; ?></a></label>
                <label class="medium-address">
                    <?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo "({$row['address2']})"; ?>
                    <?php if(!empty($row['reference'])) echo "({$row['reference']})"; ?><br />
                     <?php echo ($row['district'] || $row['city'] || $row['province']) ? "<strong>{$row['district']}</strong> - {$row['city']} / {$row['province']}" : ''; ?>
                </label>
                <label class="notes"><?php echo "<strong>De</strong>: {$row['card_from']}<br /><strong>Para</strong>: {$row['card_to']}"; ?></label>
                <label class="card"><?php if($row['card']): ?><img src="img/icon-card.png" alt="@" /><?php endif; ?></label>
                <label class="paid"><?php if($row['is_paid']) echo '<img src="img/paid.png" alt="Pago" title="Pago" /> '; ?></label>
                <label class="short-notes"><?php
                if($row['no_delivery']):
                    echo 'Sem entrega';
/*
                else:
                    if($row['delivery_at'] != '00:00:00') echo 'Entregar às ' . substr($row['delivery_at'],0,-3);
*/
                endif; ?>
                    <p><?php echo $row['notes']; ?></p>
                </label>

                <label class="actions">
                    <a href="javascript:;" class="doShow" data-id="<?php echo $row['id']; ?>" data-type="orders"><img src="img/update.png" alt="" /> Atualizar</a>
                    <a href="javascript:;" class="doDelete" data-id="<?php echo $row['id']; ?>" data-type="orders"><img src="img/remove.png" alt="" /> Deletar</a>
                </label>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php
    }





    # Print the seller report
    public function sellerReportPrint(){
        $now = date("d/m/Y \à\s H:i");
        $id_seller = (isset($_GET['sellerReportPrint']) && $_SESSION['type'] == 'admin') ? $_GET['sellerReportPrint'] : $_SESSION['id'];
        $query = $this->db->query("SELECT COUNT(1)
            FROM orders
            WHERE id_seller = $id_seller");
        $count_orders = $query->fetchColumn();

        $query = $this->db->query("SELECT name
            FROM users
            WHERE id = $id_seller");
        $user_name = $query->fetchColumn();

$html = <<<EOL
<!DOCTYPE html>
<html id="print" lang="">
<head>
    <meta charset="utf-8">
    <title>Relatório de Vendas - Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { color: #434747; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 18px; font-weight: 300; line-height: 1.4; }
h1 { color: #1f1f1c; font-size: 28px; margin-bottom: 1em; }
h1 span { font-size: 18px; }
ul { list-style: none; }
thead { display: table-header-group; }
.card, .email, .id, .paid, .total { width: 40px; }
.city, .name, .phone, .time { width: 130px; }
.description, .full-data { width: 350px; }
.header td { font-size: 16px; font-weight: bold; }

@media print{thead{display:table-header-group; margin-bottom:2px;}}
@page{margin-top:1cm;margin-left:1cm;margin-right:1cm;margin-bottom:1.5cm;}}
tbody tr.head {
    page-break-before: always;
    page-break-inside: avoid;
}
</style>
</head>
<body>
    <h1>Relatórios de vendas - $user_name ($count_orders) <span>(Emitido dia $now)</span></h1>
    <table border="1" bordercolor="black" cellpadding="10" cellspacing="0">
    <thead>
        <tr class="header">
            <th class="id">Id</th>
            <th class="name">Comprador</th>
            <th class="name">Presenteado</th>
            <th class="medium-address">Endereço</th>
            <th class="name">Etiqueta</th>
            <th class="card">Cartão</th>
            <th class="paid">Pago</th>
            <th class="notes">Notas</th>
        </tr>
    </thead>
    <tbody>
EOL;

        $query = $this->db->query("SELECT orders.id, orders.created_at, orders.no_delivery, /* orders.delivery_at, */ orders.is_paid, orders.id_seller, orders.id_buyer, orders.id_receiver, orders.card, orders.card_from, orders.card_to, u2.name as 'buyer_name', u3.name as 'gifted_name', u3.address, u3.address2, u3.reference, u3.district, u3.city, u3.province, orders.notes, d1.id_sender, u4.name as 'sender_name'
                FROM orders
                LEFT JOIN users as u2 ON orders.id_buyer = u2.id
                LEFT JOIN users as u3 ON orders.id_receiver = u3.id
                LEFT JOIN deliveries as d1 ON orders.id = d1.id_order
                LEFT JOIN users as u4 ON d1.id_sender = u4.id
                WHERE id_seller = $id_seller
                ORDER BY u2.name ASC, u3.name ASC");

        foreach($query->fetchAll(PDO::FETCH_ASSOC) as $row):
            $card_html = ''; $is_paid_html = ''; $delivery_html = '';
            if($row['card']) $card_html = 'Sim';
            if($row['is_paid']) $is_paid_html = 'Sim';
            if($row['no_delivery']):
                $delivery_html = 'Sem entrega';
/*
            else:
                if($row['delivery_at'] != '00:00:00') $delivery_html = 'Entregar às ' . substr($row['delivery_at'],0,-3);
*/
            endif;

        $address = ($row['district'] || $row['city'] || $row['province']) ? "<strong>{$row['district']}</strong> - {$row['city']} / {$row['province']}" : '';

$html .= <<<EOL
        <tr>
            <td class="id">{$row['id']}</td>
            <td class="name">{$row['buyer_name']}</td>
            <td class="name"><strong>{$row['gifted_name']}</strong></td>
            <td class="medium-address">
                {$row['address']} {$row['address2']} ({$row['reference']})<br />
                $address
            </td>
            <td class="name"><address><small>De: {$row['card_from']}<br />Para: {$row['card_to']}</small></address></td>
            <td class="card">$card_html</td>
            <td class="paid">$is_paid_html</td>
            <td class="notes">$delivery_html
                <p>{$row['notes']}</p>
            </td>
        </tr>
EOL;

    endforeach;
$html .= <<<EOL
    </tbody>
    </table>
EOL;

    echo $html;
    die();
    }




    # Records if the sheet page was printed or no
    public function sheet_printed(){
        $user_id = $_GET['sheet_id'];
        $sheet_printed = ($_GET['sheet_printed'] == 0) ? 1 : 0;

        $query = $this->db
            ->query("UPDATE users
                SET sheet_printed = $sheet_printed
                WHERE id = $user_id");
        echo $sheet_printed;
        die();
    }





    # Generate printing tags with the sender's names
    public function sendersTags(){
        ?>
<!DOCTYPE html>
<html id="print">
<head>
    <meta charset="utf-8">
    <title>Gerando etiquetas dos entregadores... - Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style-tags.css" />
</head>
<body id="tags" class="senders-tags">
    <div class="page">
        <ul class="print">
<?php
        $query = $this->db
            ->query("SELECT DISTINCT(users.id) AS id, users.name
                FROM users
                WHERE users.is_sender = 1 AND users.is_active = 1
                ORDER BY users.name ASC");
        $rs = $query->fetchAll(PDO::FETCH_ASSOC);
        $i = 0;
        foreach($rs as $row):
        $i++;
        ?>
            <li><p><?php echo $row['name']; ?></p></li>
        <?php
            if($i % 20 == 0) echo '</ul></div><div class="page-break"></div><div class="page"><ul class="print">';
        endforeach;
        ?>
        </ul>
    </div>
</body>
</html>
    <?php
        die();
    }





    # Shows div with the info of selected user
    public function showDeliveries(){

        $data = array(':id_sender' => $_GET['showDeliveries']);
        $query = $this->db
            ->prepare("SELECT id, name, address, district, city FROM users WHERE id = :id_sender");
        $query->execute($data);
        $sender_data = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->db
            ->prepare("SELECT deliveries.id_sender, deliveries.id_order, orders.id_receiver, users.name, users.mobile, users.phone, users.address, users.address2, users.reference, users.district, users.city, users.province, u2.name as sender_name, orders.id as id_order
        FROM deliveries
        INNER JOIN orders ON deliveries.id_order = orders.id
        INNER JOIN users ON orders.id_receiver = users.id
        INNER JOIN users u2 ON deliveries.id_sender = u2.id
        WHERE u2.id = :id_sender");
        $query->execute($data);
        $rs = $query->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div id="list">
            <h1>
                <img src="img/deliveries.png" alt="Ver entregas do usuário" title="Ver entregas do usuário" />
                Ver entregas de <?php echo $sender_data['name']; ?><br>
                <small><?php echo sprintf('ID %d / %s, %s - %s', $sender_data['id'], $sender_data['address'], $sender_data['district'], $sender_data['city']); ?></small>
            </h1>
            <a href="index.php?listAll=is_sender" class="button">Voltar aos entregadores</a>
            <?php if(empty($rs)): ?>
                <h2>Nenhuma entrega</h2>

            <?php else: ?>
                <a href="index.php?printDeliveries=<?php echo $rs[0]['id_sender']; ?>" class="button" target="_blank">Imprimir ficha para o entregador</a>


                <ul>
                    <li>
                         <label class="id">Id</label>
                        <label class="name">Presenteado</label>
                        <label class="full-data">Endereço da entrega</label>
                         <label class="address">Bairro</label>
                         <label class="city">Cidade</label>
                         <label class="notes">Problemas</label>
                    </li>


                    <?php foreach($rs as $row): ?>

                        <li id="deliveries-<?php echo $row['id_sender']; ?>">
                            <label class="id"><?php echo $row['id_receiver']; ?></label>
                            <label class="name tooltip"><?php echo $row['name']; ?>
                                <address><?php echo $row['mobile']; ?><?php if(!empty($row['phone']) && !empty($row['mobile'])) echo ' / '; ?><?php echo $row['phone']; ?></address>
                            </label>
                            <label class="full-data">
                                <p><?php echo $row['address']; ?> <?php if(!empty($row['address2'])) echo '(' . $row['address2'] . ')'; ?>
                                <?php if(!empty($row['reference'])) echo '(' . $row['reference'] . ')'; ?></p>
                            </label>
                            <label class="address">
                                <p><strong><?php echo $row['district']; ?></strong></p>
                            </label>
                            <label class="city">
                                <p><?php echo $row['city']; ?> / <?php echo $row['province']; ?></p>
                            </label>
                            <label class="notes">
                                <p><a class="reportProblem" href="javascript:;" data-id="<?php echo $row['id_order']; ?>"><img src="img/problem.png" alt="" />Relatar problema</a></p>
                                <p><a class="removeDelivery" href="javascript:;" data-id="<?php echo $row['id_order']; ?>"><img src="img/remove.png" alt="" /> Remover entrega</a></p>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php
    }






    # Shows div with the info of selected user
    public function showOrder(){
        $order_id = $_GET['show'];
        $query = $this->db
            ->query("SELECT * FROM orders WHERE id = $order_id");

        $row = $query->fetch(PDO::FETCH_ASSOC);
        ?>

        <h2>Alterando informações da venda #<?php echo $order_id; ?></h2>

        <form action="index.php?updateOrder=<?php echo $order_id; ?>" id="form-update-order" method="post">
            <input class="id" name="id_seller" title="Id do vendedor" type="text" value="<?php echo $row['id_seller']; ?>" />
            <input class="id" name="id_buyer" title="Id do comprador" type="text" value="<?php echo $row['id_buyer']; ?>" />
            <input class="id" name="id_receiver" title="Id do presenteado" type="text" value="<?php echo $row['id_receiver']; ?>" />
            <input class="id" name="id_product" title="Id do produto" type="text" value="<?php echo $row['id_product']; ?>" /><br />

            <label><input <?php if($row['no_delivery']) echo 'checked="checked"'; ?> id="no_delivery" name="no_delivery" type="checkbox" /> Sem entrega</label>
            <br />
            <input type="text" name="card_from" placeholder="De:" title="De:" value="<?php echo $row['card_from']; ?>" />
            <input type="text" name="card_to" placeholder="Para:" title="Para:" value="<?php echo $row['card_to']; ?>" />
            <br />
            <label><input type="checkbox" name="card" <?php if($row['card']) echo 'checked="checked"'; ?> /> Entregar cartão</label>
             <label><input id="is_paid" name="is_paid" <?php if($row['is_paid']) echo 'checked="checked"'; ?> type="checkbox" title="Pago" /> Pago</label><br/>
            <input class="full-data" name="notes" placeholder="Observações" title="Observações" type="text" value="<?php echo $row['notes']; ?>" /><br />
            <?php if(!$row['no_delivery']) echo '<div style="display:none">'; ?>
             <label><input id="collected" name="collected" <?php if($row['collected']) echo 'checked="checked"'; ?> type="checkbox" title="Pago" /> Retirada efetuada</label><br />
            <input class="full-data" id="notes2" name="notes2" placeholder="Observações 2" title="Observações 2" type="text" value="<?php echo $row['notes2']; ?>" />
            <?php if(!$row['no_delivery']) echo '</div>'; ?>
            <br />
            <input type="submit" value="Atualizar" />
        </form>

        <?php
        die();
    }






    # Shows div with the info of selected product
    public function showProduct(){
        $product_id = $_GET['showProduct'];
        $query = $this->db
            ->query("SELECT * FROM products WHERE id = $product_id");

        $row = $query->fetch(PDO::FETCH_ASSOC);
        ?>
        <h2>Alterando informações de "<?php echo $row['name']; ?>" (id <?php echo $product_id; ?>)</h2>
        <form action="index.php?updateProduct=<?php echo $product_id; ?>" id="form-update-product" method="post">
            <input id="name" name="name" placeholder="Nome" type="text" value="<?php echo $row['name']; ?>" />
            <input id="price" name="price" placeholder="Preço" type="text" value="<?php echo $row['price']; ?>" />
            <input id="supply" name="supply" placeholder="Estoque inicial" type="text" value="<?php echo $row['supply']; ?>" />
            <input type="submit" value="Atualizar" />
        </form>

        <?php
        die();
    }





    # Shows div with the info of selected user
    public function showUser(){
        $user_id = $_GET['show'];
        $query = $this->db
            ->query("SELECT * FROM users WHERE id = $user_id");

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $required = ($row['is_sender']) ? 'required' : '';
        ?>
        <h2>Alterando informações de "<?php echo $row['name']; ?>" (id <?php echo $user_id; ?>)</h2>
        <div id="map-canvas"></div>
        <form action="index.php?update=<?php echo $user_id; ?>" id="form-update-user" method="post" onsu bmit="$('body').on('submit','#form-update-user',function(){if($('#no-phone').is(':checked')){$('#mobile').val('(00) 0000-0000');}});">
            <input id="name" name="name" placeholder="Nome" required title="Digite o nome" type="text" value="<?php echo $row['name']; ?>" />
            <input id="email" name="email" placeholder="Email" title="Email" type="email" value="<?php echo $row['email']; ?>" /><br />
            <input class="mobile" id="mobile" name="mobile" placeholder="Telefone principal" required title="Digite o número do telefone principal" type="text" value="<?php echo $row['mobile']; ?>" />
            <input class="validate-phone" id="phone" name="phone" placeholder="Outro telefone" title="Preencha caso haja outro telefone" type="text" value="<?php echo $row['phone']; ?>" />
            <label><input id="no-phone" type="checkbox" /> Usar telefone do comprador</label><br /><br />
            <input id="address" name="address" placeholder="Endereço" <?php echo $required; ?> title="Digite o endereço" type="text" value="<?php echo $row['address']; ?>" />
            <input id="address2" name="address2" placeholder="Complemento" type="text" value="<?php echo $row['address2']; ?>" />
            <input id="reference" name="reference" placeholder="Referência" title="Digite uma referência do endereço" type="text" value="<?php echo $row['reference']; ?>" /><br />
            <input class="district" id="district" name="district" placeholder="Bairro" <?php echo $required; ?> title="Digite o bairro" type="hidden" value="<?php echo $row['district']; ?>" />
<!--			<input id="city" name="city" placeholder="Cidade" <?php echo $required; ?> title="Digite a cidade" type="text" value="<?php echo $row['city']; ?>" />-->
            <input id="city" name="city" <?php echo $required; ?> type="hidden">
<!--
                <option disabled="disabled" <?php if($row['city'] == '') echo 'selected="selected"'; ?>>Cidade</option>
                <option <?php if($row['city'] == 'Belo Horizonte') echo 'selected="selected"'; ?>>Belo Horizonte</option>
                <option <?php if($row['city'] == 'Betim') echo 'selected="selected"'; ?>>Betim</option>
                <option <?php if($row['city'] == 'Contagem') echo 'selected="selected"'; ?>>Contagem</option>
                <option <?php if($row['city'] == 'Lagoa Santa') echo 'selected="selected"'; ?>>Lagoa Santa</option>
                <option <?php if($row['city'] == 'Nova Lima') echo 'selected="selected"'; ?>>Nova Lima</option>
                <option <?php if($row['city'] == 'Ribeirão das Neves') echo 'selected="selected"'; ?>>Ribeirão das Neves</option>
                <option <?php if($row['city'] == 'Sabará') echo 'selected="selected"'; ?>>Sabará</option>
                <option <?php if($row['city'] == 'Santa Luzia') echo 'selected="selected"'; ?>>Santa Luzia</option>
            </select>
-->
            <input id="province" name="province" placeholder="UF" title="Digite a sigla do estado" type="hidden" value="<?php echo $row['province']; ?>" />
            <input id="notes" name="notes" placeholder="Notas" title="Notas" type="text" value="<?php echo $row['notes']; ?>" />
            <input id="geolat" name="geolat" type="hidden" />
            <input id="geolng" name="geolng" type="hidden" />


            <?php
            if($row['is_sender'] == 1):
                $query = $this->db
                    ->query("SELECT * FROM senders WHERE id = $user_id");

                $row = $query->fetch(PDO::FETCH_ASSOC);
             ?>

            <p>
                <label>Preferência de entrega nos bairros <small>(opcional)</small></label>
            </p>
            <div id="box-districts" class="opened">
                <input class="district" id="district1" name="district1" placeholder="Bairro 1" type="text" value="<?php echo $row['district1']; ?>" />
                <input class="district" id="district2" name="district2" placeholder="Bairro 2" type="text" value="<?php echo $row['district2']; ?>" />
                <input class="district" id="district3" name="district3" placeholder="Bairro 3" type="text" value="<?php echo $row['district3']; ?>" />
                <input class="district" id="district4" name="district4" placeholder="Bairro 4" type="text" value="<?php echo $row['district4']; ?>" />
                <input class="district" id="district5" name="district5" placeholder="Bairro 5" type="text" value="<?php echo $row['district5']; ?>" />
            </div>
            <?php endif; ?>

            <input type="submit" value="Atualizar" />
        </form>

        <?php
        die();
    }






    # Generate printing tags
    public function tags(){
        ?>
<!DOCTYPE html>
<html id="print">
<head>
    <meta charset="utf-8">
    <title>Gerando etiquetas... - Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style-tags.css" />
</head>
<body id="tags">
<div class="page">
<?php

        $query = $this->db
            ->query("SELECT DISTINCT(users.id) AS id, users.name
                FROM orders
                INNER JOIN deliveries ON orders.id = deliveries.id_order
                INNER JOIN users ON deliveries.id_sender = users.id
                WHERE users.is_active = 1
                ORDER BY users.name");
        $rs = $query->fetchAll(PDO::FETCH_ASSOC);


        $page = 0;
        $header_or_footer = 0;
        foreach($rs as $row):
        ?>

            <ul class="print">
            <?php if (!($header_or_footer & 1)): ?><li class="header"><?php echo $row['name']; ?></li><li class="invisible"></li><?php endif; ?>
            <?php
            $query = $this->db
                ->query("SELECT orders.card_from, orders.card_to
                    FROM orders
                    INNER JOIN deliveries ON orders.id = deliveries.id_order
                    WHERE deliveries.id_sender = {$row['id']}");
            $rs_tags = $query->fetchAll(PDO::FETCH_ASSOC);

            for($i = 0; $i < 10; $i++):
                $text_card = '';
                if(!empty($rs_tags[$i]['card_from']) || !empty($rs_tags[$i]['card_to'])) $text_card = "<p><strong>De:</strong> {$rs_tags[$i]['card_from']}<br /><strong>Para:</strong> {$rs_tags[$i]['card_to']}</p>";
                 echo "<li>$text_card</li>";
            endfor; ?>

            <?php if($header_or_footer & 1): ?><li class="footer"><?php echo $row['name']; ?></li><li class="invisible"></li><?php endif; ?>
            </ul>

            <?php
            if($page & 1) echo '<div class="page-break"></div>';
            $page++;
            $header_or_footer++;
            ?>

        <?php endforeach; ?>
<!--
    <div class="page-break"></div>
    <div class="screen">
        <h3>As etiquetas só serão visíveis na impressão</h3>
        <ul>
            <?php
            $i = 1;
            foreach($rs as $row):
                $page = ceil($i / 2);
                $page_position = ($i & 1) ? 'superior' : 'inferior';
                echo "<li><p>Página $page $page_position: {$row['name']}</p></li>";
                $i++;
            endforeach;
            ?>
        </ul>
    </div>
-->
</div>
</body>
</html>
    <?php
        die();
    }





    # Logs when users begin and finish making the deliveries
    public function trackDelivery(){
        $field = 'date_' . $_GET['trackDelivery'];
        $date = date('Y-m-d');
        @$this->db
            ->prepare("UPDATE senders SET $field = '$date {$_GET['time']}' WHERE id = {$_GET['id']}")
            ->execute();
            ?>
            <script type="text/javascript">
            window.location.href = 'index.php?adminSenders'
            </script>
            <?php
            die();
    }




    # Updates order info
    public function updateOrder(){
        $no_delivery = 0; $is_paid = 0; $card = 0; $collected = 0;
        if(isset($_POST['no_delivery']) == 'on') $no_delivery = 1;
        if(isset($_POST['is_paid']) == 'on') $is_paid = 1;
        if(isset($_POST['card']) == 'on') $card = 1;
        if(isset($_POST['collected']) == 'on') $collected = 1;

        $data = array(
            ':id_seller' => $_POST['id_seller'],
            ':id_buyer' => $_POST['id_buyer'],
            ':id_receiver' => $_POST['id_receiver'],
            ':id_product' => $_POST['id_product'],
            ':no_delivery' => $no_delivery,
            ':is_paid' => $is_paid,
            ':card' => $card,
            ':card_from' => $_POST['card_from'],
            ':card_to' => $_POST['card_to'],
            ':notes' => $_POST['notes'],
            ':collected' => $collected,
            ':notes2' => $_POST['notes2'],
            ':order_id' => $_GET['updateOrder']
            );

        @$this->db
            ->prepare("UPDATE orders
                SET id_seller = :id_seller, id_buyer = :id_buyer, id_receiver = :id_receiver, id_product = :id_product, no_delivery = :no_delivery, is_paid = :is_paid, card = :card, card_from = :card_from, card_to = :card_to, notes = :notes, collected = :collected, notes2 = :notes2
                WHERE id = :order_id")
            ->execute($data);
        header('location: ' . $_SERVER['HTTP_REFERER']);
    }




    # Updates user info
    public function updateProduct(){
        $data = array(
            ':name' => $_POST['name'],
            ':price' => $_POST['price'],
            ':supply' => $_POST['supply'],
            ':user_id' => $_GET['updateProduct']
            );
        @$this->db
            ->prepare("UPDATE products
                SET name = :name, price = :price, supply = :supply
                WHERE id = :user_id")
            ->execute($data);
    }





    # Updates user info
    public function updateUser(){
        $data = array(
            ':name' => $_POST['name'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':mobile' => $_POST['mobile'],
            ':address' => $_POST['address'],
            ':address2' => $_POST['address2'],
            ':reference' => $_POST['reference'],
            ':district' => $_POST['district'],
            ':city' => $_POST['city'],
            ':province' => $_POST['province'],
            ':notes' => $_POST['notes'],
            ':geolat' => $_POST['geolat'],
            ':geolng' => $_POST['geolng'],
            ':user_id' => $_GET['update']
            );
        @$this->db
            ->prepare("UPDATE users
                SET name = :name, email = :email, phone = :phone, mobile = :mobile, address = :address, address2 = :address2, reference = :reference, district = :district, city = :city, province = :province, notes = :notes, geolat = :geolat, geolng = :geolng
                WHERE id = :user_id")
            ->execute($data);


        if(!empty($_POST['district1']) || !empty($_POST['district2']) || !empty($_POST['district3']) || !empty($_POST['district4']) || !empty($_POST['district5'])):

            $data = array(
                ':user_id' => $_GET['update'],
                ':district1' => $_POST['district1'],
                ':district2' => $_POST['district2'],
                ':district3' => $_POST['district3'],
                ':district4' => $_POST['district4'],
                ':district5' => $_POST['district5']
                );
            @$this->db
                ->prepare("INSERT INTO senders (id, district1, district2, district3, district4, district5)
                    VALUES (:user_id, :district1, :district2, :district3, :district4, :district5)
                      ON DUPLICATE KEY UPDATE district1 = :district1, district2 = :district2, district3 = :district3, district4 = :district4, district5 = :district5")
                ->execute($data);
        endif;


        header('location: ' . $_SERVER['HTTP_REFERER']);
    }





    # Updates user function
    public function updateUserFunction(){
        $is_seller = (isset($_POST['is_seller']) && $_POST['is_seller'] == 'on') ? 1 : 0;
        $is_buyer = (isset($_POST['is_buyer']) && $_POST['is_buyer'] == 'on') ? 1 : 0;
        $is_sender = (isset($_POST['is_sender']) && $_POST['is_sender'] == 'on') ? 1 : 0;
        $is_gifted = (isset($_POST['is_gifted']) && $_POST['is_gifted'] == 'on') ? 1 : 0;

        $data = array(
            ':is_seller' => $is_seller,
            ':is_buyer' => $is_buyer,
            ':is_sender' => $is_sender,
            ':is_gifted' => $is_gifted,
            ':user_id' => $_GET['updateUserFunction']
            );
        @$this->db
            ->prepare("UPDATE users
                SET is_seller = :is_seller, is_buyer = :is_buyer, is_sender = :is_sender, is_gifted = :is_gifted
                WHERE id = :user_id")
            ->execute($data);



        if($is_gifted):
            $data = array(
                ':address' => $_POST['address'],
                ':address2' => $_POST['address2'],
                ':reference' => $_POST['reference'],
                ':district' => $_POST['district'],
                ':city' => $_POST['city'],
                ':province' => $_POST['province'],
                ':geolat' => $_POST['geolat'],
                ':geolng' => $_POST['geolng'],
                ':user_id' => $_GET['updateUserFunction']
                );

            @$this->db
                ->prepare("UPDATE users
                    SET address = :address, address2 = :address2, reference = :reference, district = :district, city = :city, province = :province, geolat = :geolat, geolng = :geolng
                    WHERE id = :user_id")
                ->execute($data);

        endif;



        if(!empty($_POST['district1']) || !empty($_POST['district2']) || !empty($_POST['district3']) || !empty($_POST['district4']) || !empty($_POST['district5'])):

            $data = array(
                ':user_id' => $_GET['updateUserFunction'],
                ':district1' => $_POST['district1'],
                ':district2' => $_POST['district2'],
                ':district3' => $_POST['district3'],
                ':district4' => $_POST['district4'],
                ':district5' => $_POST['district5']
                );
            @$this->db
                ->prepare("INSERT INTO senders (id, district1, district2, district3, district4, district5)
                    VALUES (:user_id, :district1, :district2, :district3, :district4, :district5)
                      ON DUPLICATE KEY UPDATE district1 = :district1, district2 = :district2, district3 = :district3, district4 = :district4, district5 = :district5")
                ->execute($data);
        endif;



        header('location: index.php?successUpdateUser=1');
    }





    # Verifies if the email is already taken by another user
    public function verifyBuyer(){
        $verify_error = '';
        echo '<div id="list"><h2>Adicionar nova venda</h2>';
        $data = array(':name' => $_POST['buyer']);
        $query = $this->db
            ->prepare("SELECT id, name
                FROM users
                WHERE name = :name AND is_buyer = 1");
        $query->execute($data);
        $row_buyer = $query->fetch(PDO::FETCH_ASSOC);
            if(empty($row_buyer)):
                echo '<p class="error">Nenhum usuário com o nome "' . $_POST['buyer'] . '" ou o usuário não é comprador.</p>';
                $verify_error = true;
            endif;

        $data = array(':name' => $_POST['gifted']);
        $query = $this->db
            ->prepare("SELECT id, name
                FROM users
                WHERE name = :name AND is_gifted = 1");
        $query->execute($data);
        $row_gifted = $query->fetch(PDO::FETCH_ASSOC);

            if(empty($row_gifted)):
                echo '<p class="error">Nenhum usuário com o nome "' . $_POST['gifted'] . '" ou o usuário não é presenteado.</p>';
                $verify_error = true;
            endif;


            if($verify_error):
                echo '<a href="javascript:;" class="new-user">Adicione aqui os dados do(s) usuário(s) ou altere sua(s) função(ões)</a> e depois faça a compra.</div>';
            else:
    ?>
    <strong>Vendedor</strong>: <?php echo $_SESSION['id']; ?> - <?php echo $_SESSION['name']; ?> <br />
    <strong>Comprador</strong>: <?php echo $row_buyer['id']; ?> - <?php echo $row_buyer['name']; ?> <br />
    <strong>Presenteado</strong>: <?php echo $row_gifted['id']; ?> - <?php echo $row_gifted['name']; ?> <br />
    <form action="index.php?addOrder=1" id="form-add-order" method="post">
        <input name="id_buyer" type="hidden" value="<?php echo $row_buyer['id']; ?>"/>
        <input name="id_gifted" type="hidden" value="<?php echo $row_gifted['id']; ?>"/>


        <h3>Dados do produto</h3>
        <?php
        $query = $this->db
            ->query("SELECT * FROM products WHERE out_of_stock IS NULL");
        $rs = $query->fetchAll(PDO::FETCH_ASSOC);
        $count_products = $query->rowCount();

        foreach($rs as $row): ?>
            <label><input <?php if($count_products == 1) echo 'checked="checked"'; ?> name="id_product" required type="radio" value="<?php echo $row['id']; ?>" /> <?php echo $row['name']; ?> R$<?php echo number_format($row['price'],2,',','.'); ?></label>
        <?php endforeach; ?>
        <label><input id="is_paid" name="is_paid" type="checkbox" /> Pagamento recebido</label><br />

        <h3>Dados da entrega</h3>
<!-- 		<input class="delivery_at" id="delivery_at" name="delivery_at" placeholder="Horário da entrega" type="text" /> -->
        <label><input <?php if($_POST['gifted'] == 'Sem entrega') echo 'checked="checked"'; ?> id="no_delivery" name="no_delivery" type="checkbox" /> Sem entrega</label><br />

        <h3>Dados da etiqueta</h3>
        <input type="text" id="card_from" name="card_from" placeholder="De:" title="De:" />
        <input type="text" id="card_to" name="card_to" placeholder="Para:" title="Para:" />

        <h3>Dados do cartão</h3>
        <label><input type="checkbox" name="card" /> Entregar cartão</label>

        <h3>Dados da venda</h3>
        <input type="text" class="notes" id="notes" name="notes" placeholder="Observações" /> <br /><br />

        <input type="submit" value="Concluir venda" />

    </form>
    </div>
<?php
        endif;
    }





    # Verifies if the user exists and make him/her key sender
    public function verifyKeySender(){
        $data = array(':name' => $_POST['name']);
        $query = $this->db
            ->prepare("SELECT id, name
                FROM users
                WHERE name = :name");
        $query->execute($data);

            if($row = $query->fetch(PDO::FETCH_ASSOC)):
                $data = array(':id_sender' => $row['id']);
                $query = $this->db
                    ->prepare("SELECT id
                        FROM key_senders
                        WHERE id = :id_sender");
                $query->execute($data);
                if(!$is_repeated = $query->fetch(PDO::FETCH_ASSOC)):
                    @$this->db
                        ->prepare("INSERT INTO key_senders (id) VALUES (:id_sender)")
                        ->execute($data);
                endif;
            ?>
            <div id="list">
                <h2><img src="img/add-key-sender.png" alt="Adicionar entregador-chave" title="Adicionar entregador-chave" /> Usuário promovido para entregador-chave.</h2>
                <a href="index.php?listKeySenders">Veja todos entregadores</a>
            </div>


        <?php else: ?>
            <div id="list">
                <h2><img src="img/add-key-sender.png" alt="Adicionar entregador-chave" title="Adicionar entregador-chave" /> Nenhum usuário com esse nome</h2>
                <a href="javascript:;" class="new-key-sender">Tente novamente</a>
            </div>
        <?php
        endif;
    }





    # Verify if a new order
    public function verifyOrder(){
        $query = $this->db
            ->query("SELECT DISTINCT(u1.id) as id_buyer, (
                        SELECT DISTINCT(u2.id)
                        FROM orders
                        RIGHT JOIN users as u2 ON u2.id = orders.id_receiver
                        WHERE u2.name = '{$_GET['gifted']}'
                    ) as id_receiver
                    FROM orders
                    RIGHT JOIN users as u1 ON u1.id = orders.id_buyer
                    WHERE u1.name = '{$_GET['buyer']}'");
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $id_buyer = $row['id_buyer'];
        $id_receiver = ($row['id_receiver']) ? $row['id_receiver'] : 0;
        $query = $this->db
            ->query("SELECT COUNT(1) FROM orders
                WHERE id_buyer = $id_buyer AND id_receiver = $id_receiver");
        $row = $query->fetchColumn();

        header('Content-type: application/json');

        if($row > 0):
            echo json_encode( array('repeatedOrder' => true ));
        else:
            echo json_encode( array('repeatedOrder' => false ));
        endif;

        die();
    }





    # Verifies if the email is already taken by another user
    public function verifyUser(){
        $data = array(':name' => $_POST['name']);
        $query = $this->db
            ->prepare("SELECT *
                FROM users
                WHERE name = :name");
        $query->execute($data);

            if($row = $query->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div id="list">
            <h2>Esse usuário já existe.</h2>
            <h3>Mas você pode acrescentar funções a esse usuário</h3>
            <div id="map-canvas"></div>
            <form action="index.php?updateUserFunction=<?php echo $row['id']; ?>" id="form-update-user-function" method="post">
                <input disabled="disabled" id="name" name="name" placeholder="Nome" type="text" value="<?php echo $row['name']; ?>" />
                <input class="mobile" disabled="disabled" id="mobile" name="mobile" placeholder="Telefone principal" type="text" value="<?php echo $row['mobile']; ?>" />
                <input class="validate-phone" disabled="disabled" id="phone" name="phone" placeholder="Outro telefone" type="text" value="<?php echo $row['phone']; ?>" />

                <label><input id="no-phone" type="checkbox" /> Usar telefone do comprador</label><br />

                <div id="form-gifted-address" <?php if($row['is_gifted'] || $row['is_sender']) echo 'style="display:block"'; ?>>
                    <input class="gifted-address" id="address" name="address" placeholder="Endereço" type="text" value="<?php echo $row['address']; ?>" />
                    <input cla ss="gifted-address" name="gifted-address2" placeholder="Complemento" type="text" value="<?php echo $row['address2']; ?>" />
                    <input id="reference" name="reference" placeholder="Referência" type="text" value="<?php echo $row['reference']; ?>" /><br />
                    <input class="district gifted-address" id="district" name="district" placeholder="Bairro" type="hidden" value="<?php echo $row['district']; ?>" />
<!--					<input class="gifted-address" id="city" name="city" placeholder="Cidade" type="text" value="<?php echo $row['city']; ?>" />-->
                <input class="gifted-address" id="city" name="city" <?php echo $required; ?> type="hidden">
<!--
                    <option disabled="disabled" <?php if($row['city'] == '') echo 'selected="selected"'; ?>>Cidade</option>
                    <option <?php if($row['city'] == 'Belo Horizonte') echo 'selected="selected"'; ?>>Belo Horizonte</option>
                    <option <?php if($row['city'] == 'Betim') echo 'selected="selected"'; ?>>Betim</option>
                    <option <?php if($row['city'] == 'Contagem') echo 'selected="selected"'; ?>>Contagem</option>
                    <option <?php if($row['city'] == 'Lagoa Santa') echo 'selected="selected"'; ?>>Lagoa Santa</option>
                    <option <?php if($row['city'] == 'Nova Lima') echo 'selected="selected"'; ?>>Nova Lima</option>
                    <option <?php if($row['city'] == 'Ribeirão das Neves') echo 'selected="selected"'; ?>>Ribeirão das Neves</option>
                    <option <?php if($row['city'] == 'Sabará') echo 'selected="selected"'; ?>>Sabará</option>
                    <option <?php if($row['city'] == 'Santa Luzia') echo 'selected="selected"'; ?>>Santa Luzia</option>
                </select>
-->
                    <input class="gifted-address" id="province" name="province" placeholder="UF" type="hidden" value="MG" value="<?php echo $row['province']; ?>" />
                </div>
                <input id="geolat" name="geolat" type="hidden" value="<?php echo $row['geolat']; ?>" />
                <input id="geolng" name="geolng" type="hidden" value="<?php echo $row['geolng']; ?>" />

                <div id="box-districts" <?php if($row['is_sender']) echo 'class="opened"'; ?>>
                    <p>
                        <label>Preferência de entrega nos bairros <small>(opcional)</small></label>
                    </p>
                <?php
                $data = array(':id' => $row['id']);
                $query = $this->db
                    ->prepare("SELECT district1, district2, district3, district4, district5
                        FROM senders
                        WHERE id = :id");
                $query->execute($data);

                $districts = $query->fetch(PDO::FETCH_ASSOC); ?>
                    <input class="district" id="district1" name="district1" placeholder="Bairro 1" type="text" value="<?php echo $districts['district1']; ?>" />
                    <input class="district" id="district2" name="district2" placeholder="Bairro 2" type="text" value="<?php echo $districts['district2']; ?>" />
                    <input class="district" id="district3" name="district3" placeholder="Bairro 3" type="text" value="<?php echo $districts['district3']; ?>" />
                    <input class="district" id="district4" name="district4" placeholder="Bairro 4" type="text" value="<?php echo $districts['district4']; ?>" />
                    <input class="district" id="district5" name="district5" placeholder="Bairro 5" type="text" value="<?php echo $districts['district5']; ?>" />
                </div>


                <p>
                    <label><input class="user-type" <?php if($row['is_seller']) echo 'checked="checked"'; ?> name="is_seller" type="checkbox" /> Vendedor</label>
                    <label><input class="user-type" <?php if($row['is_buyer']) echo 'checked="checked"'; ?> name="is_buyer" type="checkbox" /> Comprador</label>
                    <label><input class="user-type" id="open-box-districts" <?php if($row['is_sender']) echo 'checked="checked"'; ?> name="is_sender" type="checkbox" /> Entregador</label>
                    <label><input class="user-type" id="is-gifted" <?php if($row['is_gifted']) echo 'checked="checked"'; ?> name="is_gifted" type="checkbox" /> Presenteado</label>
                </p>


                <input type="submit" value="Alterar função" />
            </form>
            </div>


        <?php else: ?>
            <div id="list">
            <h2>Adicionar novo usuário</h2>
            <div id="map-canvas"></div>
            <form action="index.php?addUser=1" id="form-add-user" method="post" onsubmit="if($('#no-phone').is(':checked')){$('#mobile').val('(00) 0000-0000');}">
                <input name="name" readonly="readonly" type="text" value="<?php echo $_POST['name']; ?>" />
                <input id="email" name="email" placeholder="Email" type="email" />
                <input id="password" name="password" placeholder="Senha" type="password" /><br />
                <input class="mobile" id="mobile" name="mobile" placeholder="Telefone principal" required type="text" />
                <input class="validate-phone" id="phone" name="phone" placeholder="Outro telefone" type="text" />
                <label><input id="no-phone" type="checkbox" /> Usar telefone do comprador</label><br />
                <input class="gifted-address" id="address" name="address" placeholder="Endereço" type="text" />
                <input id="address2" name="address2" placeholder="Complemento" type="text" />
                <input id="reference" name="reference" placeholder="Referência" type="text" /><br />
                <input class="district gifted-address" id="district" name="district" placeholder="Bairro" type="hidden" />
<!--				<input class="gifted-address" id="city" name="city" placeholder="Cidade" type="text" />-->
                <input class="gifted-address" id="city" name="city" type="hidden">
<!--
                    <option disabled="disabled" selected="selected">Cidade</option>
                    <option>Belo Horizonte</option>
                    <option>Betim</option>
                    <option>Contagem</option>
                    <option>Lagoa Santa</option>
                    <option>Nova Lima</option>
                    <option>Ribeirão das Neves</option>
                    <option>Sabará</option>
                    <option>Santa Luzia</option>
                </select>
-->
                <input class="gifted-address" id="province" name="province" placeholder="UF" type="hidden" value="MG" />
                <input id="notes" name="notes" placeholder="Notas" type="text" />
                <input id="geolat" name="geolat" type="hidden" />
                <input id="geolng" name="geolng" type="hidden" />
                <p>
                    <label><input class="user-type" name="is_seller" type="checkbox" /> Vendedor</label>
                    <label><input class="user-type" name="is_buyer" type="checkbox" /> Comprador</label>
                    <label><input class="user-type" id="open-box-districts" name="is_sender" type="checkbox" /> Entregador</label>
                    <label><input class="user-type" id="is-gifted" name="is_gifted" type="checkbox" /> Presenteado</label>
                </p>

                <div id="box-districts">
                    <p> Preferência de entrega nos bairros <small>(opcional)</small></p>
                    <input class="district" id="district1" name="district1" placeholder="Bairro 1" type="text" />
                    <input class="district" id="district2" name="district2" placeholder="Bairro 2" type="text" />
                    <input class="district" id="district3" name="district3" placeholder="Bairro 3" type="text" />
                    <input class="district" id="district4" name="district4" placeholder="Bairro 4" type="text" />
                    <input class="district" id="district5" name="district5" placeholder="Bairro 5" type="text" />
                </div>

                <input type="submit" value="Novo usuário" />


            </form>
            </div>
        <?php
        endif;
    }





    # Verify if user has multiple roles
    public function verifyUserFunction(){
        $query = $this->db
            ->query("SELECT (is_seller + is_buyer + is_sender + is_gifted) as user_functions
                FROM users
                WHERE id = {$_GET['id']}");
        $user_functions = $query->fetchColumn();

        header('Content-type: application/json');
        echo json_encode( array('numberFunctions'=> $user_functions ) );

        die();
    }





}
?>
