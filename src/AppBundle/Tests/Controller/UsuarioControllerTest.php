<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Tests\Controller;

use AppBundle\Test\BaseTestCase;

/**
 * Test funcional para comprobar que funciona bien el registro de usuarios
 * en el frontend, además del perfil y el proceso de baja del usuario.
 */
class UsuarioControllerTest extends BaseTestCase
{
    /**
     * @dataProvider usuarios
     */
    public function testRegistroPerfilBaja($usuario)
    {
        $client = static::createClient();
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/');

        // Registrarse como nuevo usuario
        $enlaceRegistro = $crawler->selectLink('Regístrate ya')->link();
        $crawler = $client->click($enlaceRegistro);

        $this->assertGreaterThan(0, $crawler->filter('html:contains("Regístrate gratis como usuario")')->count(),
            'Después de pulsar el botón Regístrate de la portada, se carga la página con el formulario de registro'
        );

        $formulario = $crawler->selectButton('Registrarme')->form($usuario);
        $crawler = $client->submit($formulario);

        $this->assertTrue($client->getResponse()->isSuccessful());

        // Comprobar que el cliente ahora dispone de una cookie de sesión
        $this->assertRegExp('/(\d|[a-z])+/', $client->getCookieJar()->get('MOCKSESSID', '/', 'localhost')->getValue(),
            'La aplicación ha enviado una cookie de sesión'
        );

        // Acceder al perfil del usuario recién creado
        $perfil = $crawler->filter('aside section#login')->selectLink('Ver mi perfil')->link();
        $crawler = $client->click($perfil);

        $this->assertEquals(
            $usuario['frontend_usuario[email]'],
            $crawler->filter('form input[name="frontend_usuario[email]"]')->attr('value'),
            'El usuario se ha registrado correctamente y sus datos se han guardado en la base de datos'
        );
    }

    /**
     * Método que provee de usuarios de prueba a los tests de esta clase.
     */
    public function usuarios()
    {
        return array(
            array(
                array(
                    'frontend_usuario[nombre]' => 'Anónimo',
                    'frontend_usuario[apellidos]' => 'Apellido1 Apellido2',
                    'frontend_usuario[email]' => 'anonimo'.uniqid().'@localhost.localdomain',
                    'frontend_usuario[passwordEnClaro][first]' => 'anonimo1234',
                    'frontend_usuario[passwordEnClaro][second]' => 'anonimo1234',
                    'frontend_usuario[direccion]' => 'Mi calle, Mi ciudad, 01001',
                    'frontend_usuario[dni]' => '11111111H',
                    'frontend_usuario[numeroTarjeta]' => '123456789012345',
                    'frontend_usuario[ciudad]' => '1',
                    'frontend_usuario[permiteEmail]' => '1',
                ),
            ),
        );
    }
}
