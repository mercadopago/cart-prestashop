/**
*  @author    Mercado pago <modulos@mercadolivre.com>
*  @copyright modulos 2017
*  @license   GNU General Public License version 2
*  @version   1.1

*  www.mercadopago.com.br
*
* Languages: EN, PT
* PS version: 1.6
*
**/
function cpfCnpj(v){

    //Remove tudo o que não é dígito
    v=v.replace(/\D/g,"")

    if (v.length <= 14) { //CPF

        //Coloca um ponto entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{3})(\d)/,"$1.$2")

        //Coloca um ponto entre o terceiro e o quarto dígitos
        //de novo (para o segundo bloco de números)
        v=v.replace(/(\d{3})(\d)/,"$1.$2")

        //Coloca um hífen entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2")

    } else { //CNPJ

        //Coloca ponto entre o segundo e o terceiro dígitos
        v=v.replace(/^(\d{2})(\d)/,"$1.$2")

        //Coloca ponto entre o quinto e o sexto dígitos
        v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3")

        //Coloca uma barra entre o oitavo e o nono dígitos
        v=v.replace(/\.(\d{3})(\d)/,".$1/$2")

        //Coloca um hífen depois do bloco de quatro dígitos
        v=v.replace(/(\d{4})(\d)/,"$1-$2")

    }

    return v
}

function validaCNPJ(strCNPJ){
    strCNPJ = strCNPJ.replace('.','');
    strCNPJ = strCNPJ.replace('.','');
    strCNPJ = strCNPJ.replace('.','');
    strCNPJ = strCNPJ.replace('-','');
    strCNPJ = strCNPJ.replace('/','');

    var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
    digitos_iguais = 1;

    if (strCNPJ.length < 14 && strCNPJ.length < 15){
      return false;
    }

    for (i = 0; i < strCNPJ.length - 1; i++){
      if (strCNPJ.charAt(i) != strCNPJ.charAt(i + 1)){
        digitos_iguais = 0;
        break;
      }
    }

    if (!digitos_iguais){
      tamanho = strCNPJ.length - 2
      numeros = strCNPJ.substring(0,tamanho);
      digitos = strCNPJ.substring(tamanho);
      soma = 0;
      pos = tamanho - 7;
      for (i = tamanho; i >= 1; i--){
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2){
          pos = 9;
        }

      }
      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
      if (resultado != digitos.charAt(0)){
        return false;
      }

      tamanho = tamanho + 1;
      numeros = strCNPJ.substring(0,tamanho);
      soma = 0;
      pos = tamanho - 7;
      for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2){
          pos = 9;
        }

      }
      resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
      if (resultado != digitos.charAt(1)){
        return false;
      }

      return true;
    }else{
      return false;
    }
  }

  function validaCPF(strCPF){
    var Soma;
    var Resto;
    strCPF = strCPF.replace(/[.-\s]/g, '')
    Soma = 0;

    if (strCPF == "00000000000"){
      return false;
    }

    for (i=1; i<=9; i++){
      Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
    }

    Resto = (Soma * 10) % 11;

    if ((Resto == 10) || (Resto == 11)){
      Resto = 0;
    }

    if (Resto != parseInt(strCPF.substring(9, 10)) ){
      return false;
    }

    Soma = 0;
    for (i = 1; i <= 10; i++){
      Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
    }

    Resto = (Soma * 10) % 11;

    if ((Resto == 10) || (Resto == 11)){
      Resto = 0;
    }

    if (Resto != parseInt(strCPF.substring(10, 11) ) ){
      return false;
    }

    return true;
  }