<?php
session_start();
require('cicx.class.php');
$cicx = new CICX();

if(isset($_GET['resetPasswordEmail']) && $_GET['resetPasswordEmail']) $cicx->resetPasswordEmail();

if(isset($_GET['addDelivery'])) $cicx->addDelivery();
if(isset($_GET['addOrder']) && $_GET['addOrder']) $cicx->addOrder();
if(isset($_GET['addUser']) && $_GET['addUser']) $cicx->addUser();
if(isset($_GET['check_all'])) $cicx->check_all();
if(isset($_GET['collected'])) $cicx->collected();
if(isset($_GET['delivery_coming'])) $cicx->delivery_coming();
if(isset($_GET['delivery_leaving'])) $cicx->delivery_leaving();
if(isset($_GET['do']) && $_GET['do'] == 'login') $cicx->login();
if(isset($_GET['do']) && $_GET['do'] == 'logout') $cicx->logout();
if(isset($_GET['exportBuyers'])) $cicx->exportBuyers();
if(isset($_GET['exportEmailSenders'])) $cicx->exportEmailSenders();
if(isset($_GET['exportSendersAddresses'])) $cicx->exportSendersAddresses();
if(isset($_GET['forgetPassword'])) $cicx->forgetPassword();
if(isset($_GET['getDistricts'])) $cicx->getDistricts();
if(isset($_GET['getGiftedNames'])) $cicx->getGiftedNames();
if(isset($_GET['getNames'])) $cicx->getNames();
if(isset($_GET['getNamesBuyer'])) $cicx->getNamesBuyer();
if(isset($_GET['getNamesGifted'])) $cicx->getNamesGifted();
if(isset($_GET['getNamesSender'])) $cicx->getNamesSender();
if(isset($_GET['getUserGeo'])) $cicx->getUserGeo();
if(isset($_GET['is_active'])) $cicx->is_active();
if(isset($_GET['is_confirmed'])) $cicx->is_confirmed();
if(isset($_GET['is_paid'])) $cicx->is_paid();
if(isset($_GET['latlngdistrict'])) $cicx->latlngdistrict();
if(isset($_GET['sheet_printed'])) $cicx->sheet_printed();
if(isset($_GET['verifyOrder'])) $cicx->verifyOrder();
if(isset($_GET['verifyUserFunction'])) $cicx->verifyUserFunction();


if(isset($_SESSION['id'])):
    if(isset($_GET['addKeySender'])) $cicx->addKeySender();
    if(isset($_GET['cleanTables'])) $cicx->cleanTables();
    if(isset($_GET['deleteProblem']) && $_GET['deleteProblem']) $cicx->deleteProblem();
    if(isset($_GET['exportSellerReport'])) $cicx->exportSellerReport();
    if(isset($_GET['exportOrders'])) $cicx->exportOrders();
    if(isset($_GET['exportSenders'])) $cicx->exportSenders();
    if(isset($_GET['map'])) $cicx->map();
    if(isset($_GET['newbox'])) $cicx->newBox();
    if(isset($_GET['noteProblem'])) $cicx->noteProblem();
    if(isset($_GET['printDeliveries'])) $cicx->printDeliveries();
    if(isset($_GET['printOrders'])) $cicx->printOrders();
    if(isset($_GET['problemsReport'])) $cicx->problemsReport();
    if(isset($_GET['reportDeliveries'])) $cicx->reportDeliveries();
    if(isset($_GET['reportProblem'])) $cicx->reportProblem();
    if(isset($_GET['resetTime'])) $cicx->resetTime();
    if(isset($_GET['sellerReportPrint'])) $cicx->sellerReportPrint();
    if(isset($_GET['sendersTags'])) $cicx->sendersTags();
    if(isset($_GET['show']) && isset($_GET['type']) && $_GET['type'] == 'orders') $cicx->showOrder();
    if(isset($_GET['show']) && $_GET['show']) $cicx->showUser();
    if(isset($_GET['showProduct']) && $_GET['showProduct']) $cicx->showProduct();
    if(isset($_GET['tags'])) $cicx->tags();
    if(isset($_GET['trackDelivery'])) $cicx->trackDelivery();
    if(isset($_GET['update']) && $_GET['update']) $cicx->updateUser();
    if(isset($_GET['updateOrder']) && $_GET['updateOrder']) $cicx->updateOrder();
    if(isset($_GET['updateProduct']) && $_GET['updateProduct']) $cicx->updateProduct();
    if(isset($_GET['updateUserFunction'])) $cicx->updateUserFunction();
endif;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Centro Infantil Chico Xavier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/jpeg" href="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wgARCAAgACADASIAAhEBAxEB/8QAGAAAAwEBAAAAAAAAAAAAAAAABQYHAwT/xAAXAQEBAQEAAAAAAAAAAAAAAAAAAQID/9oADAMBAAIQAxAAAAFollMkxYNlAyjJF+1hViPLB3nr/8QAHBAAAgMBAAMAAAAAAAAAAAAABAUBAgMAERQx/9oACAEBAAEFAnpMjqlQssSrqB9qrLTXnWMkJgxbFQFkVbNMNqP2tKeoBrADILHxX5V6w3K1BDEcj6VomFDLwLj/xAAYEQADAQEAAAAAAAAAAAAAAAAAASECEf/aAAgBAwEBPwHSolRunT//xAAbEQACAQUAAAAAAAAAAAAAAAAAAQIDERIhIv/aAAgBAgEBPwGm+ST0KNkYs//EACkQAAIBAwIFAgcAAAAAAAAAAAECAwAREgRxExQiMUEQkSEjMlFhsfD/2gAIAQEABj8CleM2Y2UHeuHJMRZb/ek5nKR1Fsu16m05LNy7Y5H+2qUL3UZ+1MkN+MD8Nqmx1LB1ItCj+9amSZ3Z2NrGpFmNosSG2oiUHEEoaDLMZB4Jte2/pjcpB4jv+6SSTJdVH0yYn6vzWcSZLmAcjXym6vKnuK//xAAhEAEAAgIBAwUAAAAAAAAAAAABABEhMWFBUYFxkbHB0f/aAAgBAQABPyF41Mtlt+1ysDYqd2pjZGtxeIcMUd6Noc0QjRQy67MORkQND1edQHrcfAYsTfbg3zfrqDDMhqlMxQjs6XL5Bi04MLjlotDBFV/1bc8/iMmKYDDS/ZdwhWem9MU6/ZeL7n//2gAMAwEAAgADAAAAEO5W+//EABcRAAMBAAAAAAAAAAAAAAAAAAABESH/2gAIAQMBAT8QhBmSQx0Sf//EABgRAAMBAQAAAAAAAAAAAAAAAAABESEx/9oACAECAQE/EOo310VN8HDD/8QAHhABAQACAwEBAQEAAAAAAAAAAREAITFBYVFxkbH/2gAIAQEAAT8QJ7+nrBD08Ditw2ioDRTTbcrrfEQdU+DR5h3WGthBX0vcAX3r2wvTLhOnKz2KpK08uAItdD5FNqaRLHbcUtkvQgUKjCB+ExIE3YEi37v+4TJDkPY074dZsOYBdUkEHrgfyAFcRIPhIGkOXpmzQ9xHLeaIhUeQ4boj1jpm1XkvhIbn7hocP997Himf/9k=" />
</head>
<body>
<div id="lightbox"></div>


<div>
    <ul id="top-bar">
        <a href="/cicx" id="logo">CICX <span>Centro Infantil Chico Xavier</span></a>
        <?php if(isset($_SESSION['id'])): ?>
             <?php if($_SESSION['type'] == 'seller'): ?>
             <li>
                 <a href="index.php?sellerReport"><img src="img/icon-report.gif" alt="" height="16" width="16"> Relatório de vendas</a>
             </li>
             <li>
                 <a href="index.php?boxChangePassword&id=<?php echo $_SESSION['id']; ?>"><img src="img/icon-password-white.png" alt="" height="16" width="16"> Alterar senha</a>
             </li>
             <?php endif; ?>
            <li><a href="index.php?do=logout"><img src="img/logout.png" alt="Sair do sistema" height="16" width="16" /> Sair do sistema</a></li>
        <?php endif; ?>
        <div class="clearfix"></div>
    </ul>


    <?php if(!isset($_SESSION['id'])): ?>
    <div id="login">
        <div id="box-login">
            <h2>Entrar no sistema (login)</h2>
            <?php if(isset($_GET['error']) && $_GET['error'] == 'login') echo '<p class="error">Nenhum usuário encontrado / senha incorreta</p>'; ?>
            <?php if(isset($_GET['error']) && $_GET['error'] == 'forget') echo '<p class="error">Nenhum usuário encontrado com esse email. Caso não tenha cadastrado seu email como vendedor, entre em contato com o administrador.</p>'; ?>
            <?php if(isset($_GET['success'])) echo '<p class="success">Cadastro feito com sucesso</p>'; ?>
            <?php if(isset($_GET['forget'])) echo '<p class="success">Email enviado. Dentro de instantes você reberá um email com instruções para alterar sua senha.</p>'; ?>
            <?php if(isset($_GET['reset'])) echo '<p class="success">Sua senha temporária é <span style="display:block; font-size: 40px">cicx123</span>Recomendamos que você acesse o sistema e troque essa senha imediatamente no menu "Alterar senha" no canto superior direito da tela.</p>'; ?>
            <form action="index.php?do=login" id="form-login" method="post">
                <input autocomplete="off" class="autocomplete-name" name="name" placeholder="Nome" type="text" required /><br />
                <input name="password" placeholder="Senha" type="password" required /><br />
                <p><a href="javascript:$('#box-login').slideUp();$('#box-forget-password').slideDown();">Esqueci a senha</a></p>
                <input type="submit" value="Entrar" />
            </form>
            <a href="javascript:;" class="addSender">Fazer cadastro como entregador</a>
        </div>

        <div id="box-forget-password">
            <h2>Esqueci a senha</h2>
            <form action="index.php?forgetPassword" id="form-forget-password" method="post">
                <input name="forgotten-email" placeholder="Email cadastrado" type="email" required /><br />
                <input type="submit" value="Enviar a senha para o meu email" />
            </form>
        </div>

        <div id="box-sender">
            <h2>Fazer cadastro como entregador</h2>
            <div id="map-canvas"></div>
            <form action="index.php?addUser=1" id="form-add-sender" method="post">
                <input id="email" name="email" placeholder="Email" type="email" /><br />
                <input id="name" name="name" placeholder="Nome" required type="text" />
                <input id="password" name="password" placeholder="Senha" type="password" /><br />
                <input class="mobile" id="mobile" name="mobile" placeholder="Telefone principal" required type="text" />
                <input class="phone" id="phone" name="phone" placeholder="Outro telefone" type="text" /><br />
                <input id="address" name="address" placeholder="Endereço" required type="text" />
                <input id="reference" name="reference" placeholder="Referência" type="text" /><br />
                <input class="district" id="district" name="district" placeholder="Bairro" required type="hidden" />
<!--
                <input id="city" list="cities" name="city" placeholder="Cidade" required type="text" />
                <datalist id="cities">
                    <option value="Belo Horizonte">
                    <option value="Betim">
                    <option value="Contagem">
                    <option value="Nova Lima">
                    <option value="Santa Luzia">
                </datalist>
-->
                <input id="city" name="city" required type="hidden">
<!--
                    <option disabled="disabled">Cidade</option>
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
                <input id="province" name="province" placeholder="UF" type="hidden" value="MG" /><br />
                <input id="notes" name="notes" placeholder="Notas" type="text" />
                <input id="geolat" name="geolat" type="hidden" />
                <input id="geolng" name="geolng" type="hidden" />
                <input name="is_sender" type="hidden" value="on" /><br />
                <p><label>
                    <input id="open-box-districts" type="checkbox" /> Preferência de entrega nos bairros <small>(opcional)</small>
                </label></p>
                <div id="box-districts">
                    <input class="district" id="district1" name="district1" placeholder="Bairro 1" type="text" />
                    <input class="district" id="district2" name="district2" placeholder="Bairro 2" type="text" />
                    <input class="district" id="district3" name="district3" placeholder="Bairro 3" type="text" />
                    <input class="district" id="district4" name="district4" placeholder="Bairro 4" type="text" />
                    <input class="district" id="district5" name="district5" placeholder="Bairro 5" type="text" />
                </div>
                <input type="submit" value="Confirmar cadastro" />

                <a href="javascript:;" class="addSender">Fazer login como usuário</a>
            </form>
        </div>
    </div>


    <?php else: ?>
    <nav>
        <ul>
            <li class="left-menu"><em>Adicionar</em>
                <ul>
                    <?php if($_SESSION['type'] != 'user'): ?><li><a href="javascript:;" class="new-user button" id="new-user"><img src="img/add-user.png" alt="Adicionar usuário" title="Adicionar usuário" />Adicionar usuário</a></li><?php endif; ?>
                    <?php if($_SESSION['type'] == 'seller' || $_SESSION['type'] == 'admin'): ?><li><a href="index.php?formAddOrder" class="button"><img src="img/add-order.png" alt="
                    enda" title="Adicionar venda" />Adicionar venda</a></li><?php endif; ?>
                    <?php if($_SESSION['type'] == 'admin'): ?><li><a href="javascript:;" class="new-product" class="button"><img src="img/add-product.png" alt="Adicionar produto" title="Adicionar produto" />Adicionar produto</a></li>
                    <li><a href="javascript:;" class="new-key-sender" class="button"><img src="img/add-key-sender.png" alt="Adicionar entregador-chave" title="Adicionar entregador-chave" />Adicionar<br>entregador-chave</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <li><em>Visualizar</em>
                <ul>
                    <li><a href="index.php?listAll=is_seller" class="button"><img src="img/sellers.png" alt="Ver vendedores" title="Ver vendedores" />Ver vendedores</a></li>
                    <li><a href="index.php?listAll=is_buyer" class="button"><img src="img/buyers.png" alt="Ver compradores" title="Ver compradores" />Ver compradores</a></li>
                    <li><a href="index.php?listAll=is_sender" class="button"><img src="img/senders.png" alt="Ver entregadores" title="Ver entregadores" />Ver entregadores</a></li>
                    <?php if($_SESSION['type'] == 'admin'): ?><li><a href="index.php?listKeySenders" class="button"><img src="img/key-senders.png" alt="Ver entregadores-chave" title="Ver entregadores-chave" />Ver entregadores-chave</a></li><?php endif; ?>
                    <li><a href="index.php?listAll=is_gifted" class="button"><img src="img/gifteds.png" alt="Ver presenteados" title="Ver presenteados" />Ver presenteados</a></li>
                    <?php if($_SESSION['type'] == 'admin'): ?><li><a href="index.php?allDeliveries" class="button"><img src="img/deliveries.png" alt="Ver entregas" title="Ver entregas" />Ver entregas</a></li><?php endif; ?>
                    <?php if($_SESSION['type'] != 'user'): ?><li><a href="index.php?listOrders" class="button"><img src="img/orders.png" alt="Ver vendas" title="Ver vendas" />Ver vendas</a></li><?php endif; ?>
                    <?php if($_SESSION['type'] == 'admin'): ?><li><a href="index.php?listProducts" class="button"><img src="img/products.png" alt="Ver produtos" title="Ver produtos" />Ver produtos</a></li><?php endif; ?>
                </ul>
            </li>
        </ul>
    </nav>
    <div class="clearfix"></div>

    <div id="content">
        <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div id="list">
                <h3>Operação realizada com sucesso.</h3>
                <?php
                $querystring = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
                if($querystring != 'verifyBuyer' && $querystring != 'verifyUser'): ?><a href="javascript: history.back();">Clique aqui para voltar</a><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error']) && $_GET['error'] == 'gifted'): ?>
            <div id="list">
                <h3>Operação não permitida.</h3>
                <p>O endereço completo é <strong>obrigatório</strong> para usuários presenteados.</p>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['reset']) && $_GET['reset'] == 1): ?>
            <div id="list">
                <ul>
                    <h3>Banco de dados limpo.</h3>
                    <p>Tudo pronto para começar mais uma edição.</p>
                    <p>Que tal começar <a href="javascript:;" class="new-product">adicionando um produto</a>?</p>
                </ul>
            </div>
        <?php endif; ?>
        <div id="box-content"></div>
        <div id="update"></div>
        <?php
        if(isset($_SESSION['id'])):
            if(isset($_GET['addProduct']) && $_GET['addProduct']) $cicx->addProduct();
            if(isset($_GET['adminSenders'])) $cicx->adminSenders();
            if(isset($_GET['allDeliveries'])) $cicx->allDeliveries();
            if(isset($_GET['boxChangePassword'])) $cicx->boxChangePassword();
            if(isset($_GET['changePassword'])) $cicx->changePassword();
            if(isset($_GET['closestDeliveries'])) $cicx->closestDeliveries();
            if(isset($_GET['delete']) && $_GET['delete']) $cicx->delete();
            if(isset($_GET['deleteDelivery']) && $_GET['deleteDelivery']) $cicx->deleteDelivery();
            if(isset($_GET['deliveries'])) $cicx->deliveries();
            if(isset($_GET['keySender']) && $_GET['keySender']) $cicx->keySender();
            if(isset($_GET['findHarvesine'])) $cicx->findHarvesine();
            if(isset($_GET['formAddOrder'])) $cicx->formAddOrder();
            if(isset($_GET['listAll'])) $cicx->listAll();
            if(isset($_GET['listKeySenders'])) $cicx->listKeySenders();
            if(isset($_GET['listOrders'])) $cicx->listOrders();
            if(isset($_GET['listOrdersNoDelivery'])) $cicx->listOrdersNoDelivery();
            if(isset($_GET['listProblems'])) $cicx->listProblems();
            if(isset($_GET['listProducts'])) $cicx->listProducts();
            if(isset($_GET['makeDelivery'])) $cicx->makeDelivery();
            if(isset($_GET['redistributeDeliveries'])) $cicx->redistributeDeliveries();
            if(isset($_GET['removeDelivery']) && $_GET['removeDelivery']) $cicx->removeDelivery();
            if(isset($_GET['resetIsConfirmed']) && $_GET['resetIsConfirmed']) $cicx->resetIsConfirmed();
            if(isset($_GET['resetPassword']) && $_GET['resetPassword']) $cicx->resetPassword();
            if(isset($_GET['resetSheetPrinted']) && $_GET['resetSheetPrinted']) $cicx->resetSheetPrinted();
            if(isset($_POST['search'])) $cicx->search();
            if(isset($_GET['searchOrder'])) $cicx->searchOrder();
            if(isset($_GET['sellerReport'])) $cicx->sellerReport();
            if(isset($_GET['showDeliveries'])) $cicx->showDeliveries();
            if(isset($_GET['verifyBuyer'])) $cicx->verifyBuyer();
            if(isset($_GET['verifyKeySender'])) $cicx->verifyKeySender();
            if(isset($_GET['verifyUser'])) $cicx->verifyUser();
        endif;
        ?>
    </div>
</div>

<?php endif; ?>
    <script src="js/jquery.js" type="text/javascript"></script>
    <script src="js/jquery.validate.min.js" type="text/javascript"></script>
    <script src="js/jquery.maskedinput.js" type="text/javascript"></script>
    <script src="js/jquery.autocomplete.min.js" type="text/javascript"></script>
    <script src="js/messages_pt_BR.js" type="text/javascript"></script>
    <!--[if IE]><script src="js/placeholder.js" type="text/javascript"></script><![endif]-->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkK3zL6elimsbHUpGJf4eZn47QoKcRyaQ&language=pt-BR"></script>
<script type="text/javascript">
var geocoder; var map;
function initialize() {
    geocoder = new google.maps.Geocoder();
    var mapOptions = {
        zoom: 16,
        center: new google.maps.LatLng(-19.91138351415555, -43.96797180175781),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
}

google.maps.event.addDomListener(window, 'load', initialize);

function initialize2() {
    geocoder2 = new google.maps.Geocoder();
    var mapOptions = {
        zoom: 16,
        center: new google.maps.LatLng(-19.91138351415555, -43.96797180175781),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map2 = new google.maps.Map(document.getElementById('gifted-map-canvas'), mapOptions);
}
if(document.getElementById('new-gifted')) google.maps.event.addDomListener(document.getElementById('new-gifted'), 'click', initialize2);
if(document.getElementById('buyer-is-gifted')) google.maps.event.addDomListener(document.getElementById('buyer-is-gifted'), 'click', initialize2);





function verifyAddress(event,form,ajax) {
    ajax = ajax || false;
    event.preventDefault();

    var address = $('#address').val();
    var boundsw = new google.maps.LatLng(-20.191261303342795, -44.372534660937504);
    var boundne = new google.maps.LatLng(-19.54549209672402, -43.685889153125004);
    var bounds = new google.maps.LatLngBounds(boundsw, boundne);

    geocoder.geocode({ 'address': address, 'bounds': bounds }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var position = results[0].geometry.location;
            map.setCenter(position);
            var marker = new google.maps.Marker({ map: map, position: position, zoom: 16 });


            var finalAddress = results[0].formatted_address;
            var cityList = ['Belo Horizonte', 'Nova Lima', 'Contagem', 'Betim', 'Santa Luzia', 'Ribeirão das Neves', 'Lagoa Santa', 'Sabará'];
            var found = false;
            for (var i = 0, len = cityList.length; i < len; ++i) {
                if (finalAddress.indexOf(cityList[i]) != -1) {
                    found = true;
                    break;
                }
            }


            if(found === true) {
                console.log(results[0]);
                var street = ''; var number = ''; var district = ''; var city = ''; var state = '';
                for (var i=0; i<results[0].address_components.length; i++) {
                    for (var j = 0, len = results[0].address_components[i].types.length; j < len; j++) {
                        switch (results[0].address_components[i].types[j]) {
                            case "street_number":
                                number = results[0].address_components[i]; break;
                            case "route": case "premise":
                                street = results[0].address_components[i]; break;
                            case "neighborhood": case "sublocality":
                                district = results[0].address_components[i]; break;
                            case "locality": case "administrative_area_level_2":
                                city = results[0].address_components[i]; break;
                            case "administrative_area_level_1":
                                state = results[0].address_components[i]; break;
                        }
                    }
                }

                window.setTimeout(function(){
                    if(typeof street === 'undefined') { window.alert('O endereço não foi encontrado\nVerifique se digitou corretamente e tente outra vez'); return true; }
                    if(window.confirm('O endereço escolhido está correto?\n' + results[0].formatted_address)) {
                        street = (street.short_name !== undefined) ? street.short_name : '';
                        number = (number.short_name !== undefined) ? number.short_name : '';
                        district = (district.short_name !== undefined) ? district.short_name : '';

                        $('#address').val(street + ', ' + number);
                        $('#district').val(district);
                        $('#city').val(city.short_name);
                        $('#province').val(state.short_name);
                        $('#geolat').val(position.lat());
                        $('#geolng').val(position.lng());

                        if($('#is_order')) {
                            $(this).unbind(event);
                            if(ajax) {
                                $.ajax({
                                    url: $('#form-update-user').attr('action'),
                                    type: 'post',
                                    data: $('#form-update-user').serialize(),
                                    success: function(data) {
                                        $('#close-lightbox').trigger('click');
                                        window.alert('Dados alterados com sucesso.');
                                        location.reload();
                                    }
                                });
                            } else {
                                $(form).submit();
                            }



                        } else {
                            $.post(
                                $(form).attr('action'),
                                $(form).serialize(),
                                function(msg) {
                                    location.href = 'index.php?success=1';
                                }
                            );
                        }
                    }
                }, 3000);





                found = false;

            } else {
                window.alert('Endereço inválido!\n\nPor favor, confira se o endereço está completo, se foi digitado corretamente e está dentro da área de entregas');
            }

        } else {
            window.alert('Endereço inválido!\n\nPor favor, confira se o endereço está completo, se foi digitado corretamente e está dentro da área de entregas');
        }
    });
}



function verifyAddressGifted(event,form) {
    event.preventDefault();
    district = $('#gifted-district').val();
    district = district.replace(/ *\([^)]*\) */g, "");

    var address = $('#gifted-address').val();
    var boundsw = new google.maps.LatLng(-20.191261303342795, -44.372534660937504);
    var boundne = new google.maps.LatLng(-19.54549209672402, -43.685889153125004);
    var bounds = new google.maps.LatLngBounds(boundsw, boundne);

    geocoder2.geocode( { 'address': address, 'bounds': bounds }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var position = results[0].geometry.location;
            map2.setCenter(position);
            var marker = new google.maps.Marker({ map: map2, position: position, zoom: 16 });


            var finalAddress = results[0].formatted_address;
            var cityList = ['Belo Horizonte', 'Nova Lima', 'Contagem', 'Betim', 'Santa Luzia', 'Ribeirão das Neves', 'Lagoa Santa', 'Sabará'];
            var found = false;
            for (var i = 0, len = cityList.length; i < len; ++i) {
                if (finalAddress.indexOf(cityList[i]) != -1) {
                    found = true;
                    break;
                }
            }

            if(found === true) {
                console.log(results[0]);
                var street = ''; var number = ''; var district = ''; var city = ''; var state = '';
                for (var i=0; i<results[0].address_components.length; i++) {
                    for (var j = 0, len = results[0].address_components[i].types.length; j < len; j++) {
                        switch (results[0].address_components[i].types[j]) {
                            case "street_number":
                                number = results[0].address_components[i]; break;
                            case "route": case "premise":
                                street = results[0].address_components[i]; break;
                            case "neighborhood": case "sublocality":
                                district = results[0].address_components[i]; break;
                            case "locality": case "administrative_area_level_2":
                                city = results[0].address_components[i]; break;
                            case "administrative_area_level_1":
                                state = results[0].address_components[i]; break;
                        }
                    }
                }

                window.setTimeout(function(){
                    if(typeof street === 'undefined') { window.alert('O endereço não foi encontrado\nVerifique se digitou corretamente e tente outra vez'); return true; }
                    if(window.confirm('O endereço escolhido está correto?\n' + results[0].formatted_address)) {

                        street = (street.short_name !== undefined) ? street.short_name : '';
                        number = (number.short_name !== undefined) ? number.short_name : '';
                        district = (district.short_name !== undefined) ? district.short_name : '';

                        $('#gifted-address').val(street + ', ' + number);
                        $('#gifted-district').val(district);
                        $('#gifted-city').val(city.short_name);
                        $('#gifted-province').val(state.short_name);
                        $('#gifted-geolat').val(position.lat());
                        $('#gifted-geolng').val(position.lng());

                        $(this).unbind(event);
                        $(form).submit();
                    }
                }, 3000);



                found = false;

            } else {
                window.alert('Endereço inválido!\n\nPor favor, confira se o endereço está completo, se foi digitado corretamente e está dentro da área de entregas');
            }

        } else {
            window.alert('Endereço inválido!\n\nPor favor, confira se o endereço está completo, se foi digitado corretamente e está dentro da área de entregas');
        }
    });
}




$(document).ready(function() {

    function ninthDigit(){
        var phone, element;
        element = $(this);
        element.unmask();
        phone = element.val().replace(/\D/g, '');
        if(phone.length > 10) {
            element.mask("(99) 99999-999?9");
        } else {
            element.mask("(99) 9999-9999?9");
        }
    }

    $("#phone").mask("(99) 99999-999?9").change(ninthDigit).trigger('change');
    $(".validate-phone").mask("(99) 99999-999?9").change(ninthDigit).trigger('change');
    $(".mobile").mask("(99) 99999-999?9").change(ninthDigit).trigger('change');
    $(".delivery_at").mask("99:99");



    /* Functions */


    $("body").on("click",".doDelete",function(){
        if (confirm("Tem certeza que deseja excluir?")) {
            del_id = $(this).attr('data-id');
            var fadeitem = '#' + $(this).attr('data-type') + '-' + del_id;

            if($(this).attr('data-type') == 'users') {
                $.getJSON("index.php?verifyUserFunction", { 'id': del_id })
                    .done(function(json) {
                        if(json.numberFunctions > 1) {
                            if(confirm("Esse usuário tem mais de uma função.\nDeseja prosseguir assim mesmo?")) {
                                $.get('index.php', { delete: 'users', id : del_id }, function(data) {
                                    $(fadeitem).fadeOut();
                                });
                            } else {
                                return false;
                            }
                        } else {
                            $.get('index.php', { delete: 'users', id : del_id }, function(data) {
                                $(fadeitem).fadeOut();
                            });
                        }
                    }
                );
            } else {
                $.get('index.php', { delete: $(this).attr('data-type'), id : del_id }, function(data) {
                    $(fadeitem).fadeOut();
                });
            }
        }
        return false;
    });



    $("body").on("click",".removeDelivery",function() {
        $this = $(this);
        $.get('index.php', { removeDelivery: $(this).attr('data-id') }, function(data) {
            $this.parent().parent().parent().fadeOut();
        });
    });


    $("body").on("click","#reset-is-confirmed",function() {
        $this = $(this);
        if (confirm("Tem certeza que deseja desmarcar todos campos de \"dados ok\"?")) {
        $.get('index.php', { resetIsConfirmed: true }, function(data) {
            location.reload();
        });
        }
    });


    $("body").on("click",".reset-password",function(){
        if (confirm("Tem certeza que deseja redefinir a senha deste usuário?")) {
            $.get('index.php', { resetPassword: $(this).attr('data-id') }, function(data) {});
        }
        return false;
    });


    $("body").on("click","#reset-sheet-printed",function() {
        $this = $(this);
        if (confirm("Tem certeza que deseja desmarcar todas as fichas impressas?")) {
        $.get('index.php', { resetSheetPrinted: true }, function(data) {
            location.reload();
        });
        }
    });


    $("body").on("click",".doDeleteDelivery",function(){
        if (confirm("Tem certeza que deseja remover essa entrega?")) {
            var fadeitem = '#' + $(this).attr('data-type') + '-' + $(this).attr('data-delivery');
            $.get('index.php', { deleteDelivery: $(this).attr('data-type'), id : $(this).attr('data-id'), id_delivery : $(this).attr('data-delivery') }, function(data) {
                $(fadeitem).fadeOut();
            });
        }
        return false;
    });

    $(".doShow").click(function(){
        $.get('index.php', { show: $(this).attr('data-id'), type: $(this).attr('data-type') }, function(data) {
            $('#lightbox').fadeIn('fast');
            $('#box-content').fadeIn('fast');
            $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
            $("form").validate({
                rules: {
                    name: { minlength: 3 },
                    password: { minlength: 4 },
                    address: { minlength: 8 },
                    mobile: { minlength: 8 },
                    province: { minlength: 2, maxlength: 2 }
                }
            });
        }).done(function() {
            initialize();
            $("#phone").mask("(99) 99999-999?9").change(ninthDigit).trigger('change');
            $("#mobile").mask("(99) 99999-999?9").change(ninthDigit).trigger('change');
            $("#delivery_at").mask("99:99");
        })
    });





    $(".doShowProduct").click(function(){
        $.get('index.php', { showProduct: $(this).attr('data-id') }, function(data) {
            $('#lightbox').fadeIn('fast');
            $('#box-content').fadeIn('fast');
            $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
        }).done(function() { /*$("html, body").animate({ scrollTop: "60px" });*/ })
    });

    $(".makeDelivery").click(function(){
        $.get('index.php', { newbox: 'makeDelivery', id: $(this).attr('data-id') }, function(data) {
            $('#lightbox').fadeIn('fast');
            $('#box-content').fadeIn('fast');
            $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
        }).done(function() { /*$("html, body").animate({ scrollTop: "60px" });*/ })
    });

    $(".new-buyer").click(function(){
        $('#form-add-order').slideUp();
        $('#remember-gifted-name').val($('#gifted').val());
        $('#name').val($('#buyer').val());
        $('#form-add-buyer').slideDown();
        var center = map.getCenter();
        google.maps.event.trigger(map, "resize");
        map.setCenter(center);
    });

    $(".new-gifted").click(function(){
        $('#form-add-order').slideUp();
        $('#remember-buyer-name').val($('#buyer').val());
        $('#gifted-name').val($('#gifted').val());
        $('#form-add-gifted').slideDown();
        var center = map2.getCenter();
        google.maps.event.trigger(map2, "resize");
        map2.setCenter(center);
    });

    $(".new-key-sender").click(function(){
        $.get('index.php', { newbox: 'keySenders' }, function(data) {
            $('#lightbox').fadeIn('fast');
            $('#box-content').fadeIn('fast');
            $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
        }).done(function() { /*$("html, body").animate({ scrollTop: "60px" });*/ })
    });

    $(".noteProblem").click(function(){
        $.get('index.php', { newbox: 'noteProblem', id: $(this).attr('data-id') }, function(data) {
            $('#lightbox').fadeIn('fast');
            $('#box-content').fadeIn('fast');
            $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
            $("#note").trigger('focus');
        }).done(function() { /*$("html, body").animate({ scrollTop: "60px" });*/ })
    });

    $(".reportProblem").click(function(){
        $.get('index.php', { newbox: 'reportProblem', id: $(this).attr('data-id') }, function(data) {
            $('#lightbox').fadeIn('fast');
            $('#box-content').fadeIn('fast');
            $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
        }).done(function() { /*$("html, body").animate({ scrollTop: "60px" });*/ })
    });

    $("body").on("click",".trackDelivery",function(){
        var new_time = window.prompt('Hora de ' + $(this).attr('data-name-type'), $(this).attr('data-time'));
        $.get('index.php', { trackDelivery: $(this).attr('data-type'), id: $(this).attr('data-id'), time: new_time }, function(data) {
            $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
        })
        $('#box-content').fadeOut('fast');
        $('#lightbox').fadeOut('fast');
    });





    $("body").on("click",".addSender",function(){
        $('#box-login').slideToggle();
        $('#box-sender').slideToggle();
        var center = map.getCenter();
        google.maps.event.trigger(map, "resize");
        map.setCenter(center);
    });
    $("body").on("focus","#card",function(){ $('#card').select(); });
    $("body").on("click",".finish",function(){
        if (confirm("Esse é o último passo...\nConfirma que o período de vendas foi encerrado?")) { return true; } else { return false; }
    });
    $("body").on("click",".problem",function(){
        if (confirm("O problema foi resolvido?")) { return true; } else { return false; }
    });





    /* Boxes */
    $("body").on("click","#close-lightbox",function(){ $('#box-content').fadeOut('fast'); $('#lightbox').fadeOut('fast'); });
    $("body").on("click","#lightbox",function(){ $('#box-content').fadeOut('fast'); $('#lightbox').fadeOut('fast'); });
    $("body").on("click",".new-user",function(){
        $.get('index.php', { newbox: 'user' }, function(data) {$('#lightbox').fadeIn('fast'); $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data); });
        $('#box-content').slideDown();
    });
    $("body").on("click",".new-product",function(){
        $.get('index.php', { newbox: 'product' }, function(data) { $('#lightbox').fadeIn('fast'); $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data); });
        $('#box-content').slideDown();
    });
    $("body").on("click",".new-order",function(){
        $.get('index.php', { newbox: 'order' }, function(data) {$('#lightbox').fadeIn('fast'); $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data); });
        $('#box-content').slideDown();
    });
    $("body").on("click","#open-box-districts",function(){
        $('#box-districts').slideToggle().removeClass('opened');
    });
    $("body").on("click",".sheet-printed",function(){
        $this = $(this);
        $.get('index.php', {
            sheet_id: $(this).attr('data-id'), sheet_printed: $(this).attr('data-active')
        }, function(data) {
            if(data == 0){
                $this.attr('data-active', 0);
                $this.addClass('off');
            } else {
                url = '/cicx/index.php?printDeliveries=' + $this.attr('data-id');
                var printWindow = window.open(url, 'Print', 'left=200, top=200, width=950, height=500, toolbar=0, resizable=0');
                printWindow.addEventListener('load', function(){ printWindow.print();}, true);

                $this.attr('data-active', 1);
                $this.removeClass('off');
            }
        });
    });
    $("body").on("click",".is-active",function(){
        $.get('index.php', { id: $(this).attr('data-id'), is_active: $(this).is(':checked') }, function(data) { });
    });
    $("body").on("click",".is-confirmed",function(){
        $.get('index.php', { id: $(this).attr('data-id'), is_confirmed: $(this).is(':checked') }, function(data) { });
    });

    $("body").on("click",".delivery-leaving",function(){
        $.get('index.php', { id: $(this).attr('data-id'), delivery_leaving: $(this).is(':checked') }, function(data) { });
    });
    $("body").on("click",".delivery-coming",function(){
        $.get('index.php', { id: $(this).attr('data-id'), delivery_coming: $(this).is(':checked') }, function(data) { });
    });

    $("body").on("click",".is-paid",function(){
        $.get('index.php', { id: $(this).attr('data-id'), is_paid: $(this).is(':checked') }, function(data) { });
    });

    $('body').on('click','#check-all',function(){
        $.get('index.php', { check_all: $(this).is(':checked') }, function(data) { });
        $('input.is-active:checkbox').prop('checked', $(this).is(':checked'));
    });

    $('body').on('click','.box-deliveries .column',function(){
        if($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            $(this).addClass('selected');
        }
    });





    /* Auto Complete */
    $("body").on("focus",".autocomplete-name",function(){
        $(this).autocomplete({
            serviceUrl: 'index.php',
            paramName: 'getNames',
            minChars: 2,
            deferRequestBy: 300,
            onSelect: function(suggestion) { $(this).val(suggestion.name.trim()); }
        });
    });
    $("body").on("focus",".autocomplete-buyer",function(){
        $(this).autocomplete({
            serviceUrl: 'index.php',
            paramName: 'getNamesBuyer',
            minChars: 2,
            deferRequestBy: 300,
            onSelect: function(suggestion) { $(this).val(suggestion.name.trim()); }
        });
    });
    $("body").on("focus",".autocomplete-gifted",function(){
        $(this).autocomplete({
            serviceUrl: 'index.php',
            paramName: 'getNamesGifted',
            minChars: 2,
            deferRequestBy: 300,
            onSelect: function(suggestion) { $(this).val(suggestion.name.trim()); }
        });
    });
    $("body").on("focus",".autocomplete-key-sender",function(){
        $(this).autocomplete({
            serviceUrl: 'index.php',
            paramName: 'getNamesSender',
            minChars: 2,
            deferRequestBy: 300,
            onSelect: function(suggestion) { $(this).val(suggestion.name.trim()); }
        });
    });
    $("body").on("focus",".autocomplete-delivery",function(){
        $(this).autocomplete({
            serviceUrl: 'index.php',
            paramName: 'getGiftedNames',
            minChars: 2,
            deferRequestBy: 300,
            onSelect: function(suggestion) { $('.autocomplete-delivery').val(suggestion.name.trim()); }
        });
    });
    $("body").on("focus",".district",function(){
        $(this).autocomplete({
            serviceUrl: 'index.php',
            paramName: 'getDistricts',
            minChars: 2,
            deferRequestBy: 300,
        });
    });






    function doVerifyUser() {
        $.post('index.php?verifyUser', { email: $('#email').val() }, function(data) {
            $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
        });
    }

    $("body").on("submit","#verifyUser",function(){ doVerifyUser(); });
    $("body").on("submit","#form-add-sender",function(event){ if($('#address').val() != '') { return verifyAddress(event,'#form-add-sender'); } });
    $("body").on("submit","#form-add-user",function(event){
        if($('input.user-type[type=checkbox]').is(':checked') == false){
            alert('Você deve definir a função do usuário:\nVendedor, comprador, entregador ou presenteado')
            return false;
        }
        if($('#is-gifted').is(':checked') || $('#open-box-districts').is(':checked')) {
            empty_address = false;
            $('.gifted-address').each(function(index, element) { if(element.value == '') { empty_address = true; } });
        }
        if($('#address').val() != '') { return verifyAddress(event,'#form-add-user'); }
    });
    $("body").on("submit","#form-update-user",function(event){if($('#address').val() != '') { return verifyAddress(event,'#form-update-user',true); } });
    $("body").on("submit","#form-update-user-function",function(event){
        if($('input.user-type[type=checkbox]').is(':checked') == false){
            alert('Você deve definir a função do usuário:\nVendedor, comprador, entregador ou presenteado')
            return false;
        }
        if($('#address').val() != '') { return verifyAddress(event,'#form-update-user-function'); }
    });
    $("body").on("submit","#form-add-buyer",function(event){ if($('#address').val() != '') { return verifyAddress(event,'#form-add-buyer'); } });
    $("body").on("submit","#form-add-gifted",function(event){ if($('#gifted-address').val() != '') { return verifyAddressGifted(event,'#form-add-gifted'); } });



    $("body").on("submit","#form-add-order.first-step",function(event){
        event.preventDefault();
        $.getJSON("index.php?verifyOrder", { 'buyer': $('#buyer').val(), 'gifted': $('#gifted').val() })
            .done(function(json) {
                if(json.repeatedOrder == true) {
                    if(confirm("Já existe uma venda do mesmo comprador para o mesmo presenteado.\nDeseja prosseguir assim mesmo?")) {
                        $(this).unbind(event);
                        $('#form-add-order').submit();
                    } else {
                        return false;
                    }
                } else {
                    $(this).unbind(event);
                    $('#form-add-order').submit();
                }
            }
        );
    });

    $("body").on("change","#no-phone",function(event){ if($('#no-phone').is(':checked')){$('#mobile').val('(00) 0000-0000');} });
    $("body").on("change","#is-gifted",function(event){
        if($('#is-gifted').is(':checked')){
            $('.gifted-address').prop('required',true);
            $('#form-gifted-address').slideDown();
        }
        else {
            $('.gifted-address').prop('required',false);
            $('#form-gifted-address').slideUp();
        }
    });
    $("body").on("change","#open-box-districts",function(event){
        if($('#open-box-districts').is(':checked')){
            $('.gifted-address').prop('required',true);
            $('#form-gifted-address').slideDown();
        }
        else {
            $('.gifted-address').prop('required',false);
            $('#form-gifted-address').slideUp();
        }
    });


    $("body").on("change",".collected",function(event){
        $.get('index.php', { id: $(this).attr('data-id'), collected: $(this).is(':checked') }, function(data){});
        if($(this).is(':checked')){
            $.get('index.php', { show: $(this).attr('data-id'), type: $(this).attr('data-type') }, function(data) {
                $('#lightbox').fadeIn('fast');
                $('#box-content').fadeIn('fast');
                $('#box-content').html('<a href="javascript:;" id="close-lightbox">X</a>' + data);
                $("#collected").prop('checked',true);
                $("#notes2").trigger('focus');
            }).done(function() {
                initialize();
                $("#phone").mask("(99) 99999-999?9").change(ninthDigit).trigger('change');
                $("#mobile").mask("(99) 99999-999?9").change(ninthDigit).trigger('change');
            });
        }
    });


    /* Form validations */
    $("#form-add-order").validate({
        rules: {
            buyer: { minlength: 3 },
            gifted: { minlength: 3 }
        }
    });

    $("#form-add-buyer").validate({
        rules: {
            name: { minlength: 3 },
            mobile: { minlength: 8 }
        }
    });

    $("#form-add-gifted").validate({
        rules: {
            'gifted-name': { minlength: 3 },
            'gifted-address': { minlength: 8 },
            'gifted-mobile': { minlength: 8 },
            'gifted-province': { minlength: 2, maxlength: 2 }
        }
    });

    $("#form-add-sender").validate({
        rules: {
            name: { minlength: 3 },
            password: { minlength: 4 },
            address: { minlength: 8 },
            mobile: { minlength: 8 },
            province: { minlength: 2, maxlength: 2 }
        }
    });

    $("#form-change-password").validate({
        rules: {
            'new-password': { minlength: 4 },
            'confirm-password': { minlength: 4, equalTo: "#new-password" }
        }, messages: {
            'confirm-password': { equalTo: "As novas senhas não são iguais" }
        }
    });

    $("form").validate({
        rules: {
            name: { minlength: 3 },
            password: { minlength: 4 },
            address: { minlength: 8 },
            mobile: { minlength: 8 },
            province: { minlength: 2, maxlength: 2 }
        }
    });


    $(document).on('click', '#form-update-order input[type=submit]', function(event) {
        event.preventDefault();
        $.ajax({
            url: $('#form-update-order').attr('action'),
            type: 'post',
            data: $('#form-update-order').serialize(),
            success: function(data) {
                $('#close-lightbox').trigger('click');
                window.alert('Dados alterados com sucesso.');
                location.reload();
            }
        });
    });


if($('.sticky').offset()) var stickyOffset = $('.sticky').offset().top;

$(window).scroll(function(){
    var sticky = $('.sticky'), scroll = $(window).scrollTop();
    if (scroll >= stickyOffset) sticky.addClass('fixed');
    else sticky.removeClass('fixed');
    });
});


<?php if(isset($_GET['successUpdateUser'])): ?>
    window.alert('Função alterada com sucesso');
    setTimeout(function() {
        $('#new-user').trigger('click');
    },10);
<?php endif; ?>


$('#form-verify-geo').on('submit',function() { verifyGeoLatLng(event); });
function verifyGeoLatLng(event) {
    event.preventDefault();
    var address = $('#address').val();
    var boundsw = new google.maps.LatLng(-20.191261303342795, -44.372534660937504);
    var boundne = new google.maps.LatLng(-19.54549209672402, -43.685889153125004);
    var bounds = new google.maps.LatLngBounds(boundsw, boundne);
    geocoder.geocode( { 'address': address, 'bounds': bounds }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var position = results[0].geometry.location;
            map.setCenter(position);
            var marker = new google.maps.Marker({ map: map, position: position });
            location.href = 'index.php?findHarvesine&geolat=' + position.lat() + '&geolng=' + position.lng();
        } else {
            alert('Endereço não encontrado. Verifique o endereço e tente novamente.');
        }
    });
}














//function processData(allText) { 
//    var allTextLines = allText.split("\r");
//    lines = [];
//    for (var i = 0, len = allTextLines.length; i < len; i++) {
//        entries = allTextLines[i].split(';');
//        lines.push(entries[3] + " - Belo Horizonte / MG");
//    }
//    console.log(lines);
//
//
//
//    for (j = 0, len = lines.length; j < len; j++) {
//
//        var address = lines[j];
//
//        var boundsw = new google.maps.LatLng(-20.191261303342795, -44.372534660937504);
//        var boundne = new google.maps.LatLng(-19.54549209672402, -43.685889153125004);
//        var bounds = new google.maps.LatLngBounds(boundsw, boundne);
//        geocoder.geocode({'address': address, 'bounds': bounds}, function(results, status) {
//
//            if (status == google.maps.GeocoderStatus.OK) {
//                var position = results[0].geometry.location;
//                map.setCenter(position);
//                var marker = new google.maps.Marker({ map: map, position: position });
//                console.log(position.lat() + '.' +  position.lng());
//            } else {
//                console.log('Endereço não encontrado.' + j);
//            }
//        });
//    }
//
//}



$(document).ready(function() {


var geocoder; var map;
function initialize() {
    var geocoder = new google.maps.Geocoder();
    var mapOptions = {
        zoom: 16,
        center: new google.maps.LatLng(-19.91138351415555, -43.96797180175781),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

//    $.ajax({
//        type: "GET",
//        url: "Blocos.csv",
//        dataType: "text",
//        success: function(data) {processData(data);}
//     });

    doIt();

}

google.maps.event.addDomListener(window, 'load', initialize);

});

    
    
    
    
    
    
    

function cap(str){
    var cantTouchThis = {'DE' : 'de', 'DA' : 'da', 'DO' : 'do', 'DOS' : 'dos', 'DAS' : 'das'};
    return str.replace(/\w[\S.]*/g, function(txt){return cantTouchThis[txt.toUpperCase()] || txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}



function finalHour(hour) {
    arr = hour.split(':');
    arr[0] = parseInt(arr[0])+5;
    if(arr[0] > 23) {
        return '23:59';
    } else {
        return arr[0] + ':' + arr[1];
    };
}

function doIt() {
    $.getJSON('blocos2.json', function(data) {
           var blocosCarnaval = [];

           $.each(data, function(key, val) {

                var dateString = val.DATA;
                var dateParts = dateString.split("/");
                var dateObject = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0]; // month is 0-based
                var dataInicio = dateObject + ' '+ val.HORA + ':00';
                var finalHr = finalHour(val.HORA);
                var dataFim = dateObject + ' ' + finalHr + ':00';

                var lat, lng;

                var address = cap(val.RUA) + ', ' + val.NUMERO + ' ' + cap(val.BAIRRO) + ' - ' + cap(val.CIDADE);

                var boundsw = new google.maps.LatLng(-20.191261303342795, -44.372534660937504);
                var boundne = new google.maps.LatLng(-19.54549209672402, -43.685889153125004);
                var bounds = new google.maps.LatLngBounds(boundsw, boundne);
                geocoder.geocode({'address': address, 'bounds': bounds}, function(results, status) {

                    if (status == google.maps.GeocoderStatus.OK) {
                        var position = results[0].geometry.location;
                        map.setCenter(position);
                        var marker = new google.maps.Marker({ map: map, position: position });
                        lat = position.lat();
                        lng = position.lng();

                    } else {
//                        console.log('Endereço não encontrado para ' + results[0].geometry.location);
                    }


//                blocosCarnaval.push([cap(val.BLOCO), dataInicio, dataFim, cap(val.RUA) + ', ' + val.NUMERO + ' ' + cap(val.BAIRRO) + ' - ' + cap(val.CIDADE), lat, lng]);



//INSERT INTO Blocoes (Data,FotoInterna,Titulo,Endereco,Latitude,Longitude,Sobre,Chamada,DataFim)
//VALUES ('2017-02-18 10:00','','Ozadas','Rua',-19.5,-43.5,'','',2017-02-18, ),
//"(" + dataInicio + ",''," + cap(val.BLOCO),cap(val.RUA) + ', ' + val.NUMERO + ' ' + cap(val.BAIRRO) + ' - ' + cap(val.CIDADE),lat, lng,'','', dataFim);




                    bloco = {
                        "Data": dataInicio,
                        "FotoInterna":"",
                        "Titulo": cap(val.BLOCO),
                        "Endereco": cap(val.RUA) + ', ' + val.NUMERO + ' ' + cap(val.BAIRRO) + ' - ' + cap(val.CIDADE),
                        "Latitude": lat,
                        "Longitude": lng,
                        "Sobre":"",
                        "Chamada":"",
                        "DataFim": dataFim
                    };
//                    blocosCarnaval.push(bloco);
                    console.log(JSON.stringify(bloco));
                });


//                blocosCarnaval.push([cap(val.BLOCO), val.DATA, val.HORA, cap(val.RUA) + ', ' + val.NUMERO + ' ' + cap(val.BAIRRO) + ' - ' + cap(val.CIDADE), lat, lng]);

           });


//console.log(blocosCarnaval);
//console.log(JSON.stringify(blocosCarnaval));



//var ItemArray = [];
//ItemArray.push({
//    RoomName : 'RoomName', 
//    Item : []
//});
//
//ItemArray[0].Item.push("New Item");
//
//console.log(JSON.stringify(ItemArray));
        
//    console.log(blocosCarnaval);
//    alert(JSON.stringify(blocosCarnaval));
//    var exportFile = JSON.stringify(JSON.parse(blocosCarnaval), null, 4);
//    console.log(exportFile);
//var data = "text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(blocosCarnaval));
//
//$('<a href="data:' + data + '" download="data.json">download JSON</a>').appendTo('#list');

    });
}
    

    
    
    
    
    
    
    
    
//for(var i = 0, len = blocosCarnaval.length; i < 10; i++){
//    
//    blocosCarnaval[i].push('-19.9');
//    blocosCarnaval[i].push('-43.0');
//console.log(blocosCarnaval[i]);
//}
    
//    var exportFile = JSON.stringify(blocosCarnaval, null, 4);
//    console.log(exportFile);





// var geocoder;
//  var map;
//  function initialize() {
//    geocoder = new google.maps.Geocoder();
//    var latlng = new google.maps.LatLng(-34.397, 150.644);
//    var mapOptions = {
//      zoom: 8,
//      center: latlng
//    }
//    map = new google.maps.Map(document.getElementById("map"), mapOptions);
//  }

//  function codeAddress() {
//    var address = document.getElementById("address").value;
//    geocoder.geocode( { 'address': address}, function(results, status) {
//      if (status == google.maps.GeocoderStatus.OK) {
//        map.setCenter(results[0].geometry.location);
//        var marker = new google.maps.Marker({
//            map: map,
//            position: results[0].geometry.location
//        });
//      } else {
//        alert("Geocode was not successful for the following reason: " + status);
//      }
//    });
//  }








$('#form-verify-geo2').on('submit',function() { verifyGeoLatLng(event); });
function verifyGeoLatLng(event) {
    event.preventDefault();
    var address = $('#address').val();
    var boundsw = new google.maps.LatLng(-20.191261303342795, -44.372534660937504);
    var boundne = new google.maps.LatLng(-19.54549209672402, -43.685889153125004);
    var bounds = new google.maps.LatLngBounds(boundsw, boundne);
    geocoder.geocode( { 'address': address, 'bounds': bounds }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var position = results[0].geometry.location;
            map.setCenter(position);
            var marker = new google.maps.Marker({ map: map, position: position });
            window.alert(position.lat() + '.' +  position.lng());
//            location.href = 'index.php?findHarvesine&geolat=' + position.lat() + '&geolng=' + position.lng();
        } else {
            alert('Endereço não encontrado. Verifique o endereço e tente novamente.');
        }
    });
}
</script>
</body>
</html>
