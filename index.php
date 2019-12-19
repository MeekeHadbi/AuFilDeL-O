<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Au fil de l'O</title>
    <meta charset="utf8"/>
    <link rel="stylesheet" href="assets/bootstrap.css"/>
    <link rel="stylesheet" href="assets/style.css"/>
    <link rel="stylesheet" href="http://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css"/>
</head>
<style media="screen">
  body{
    background-color: #828A8A;
  }
</style>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <h2 style="text-align: center; font-style:gras;">Au fil de l'O</h2><br/>
            <table style="margin-left: auto; margin-right: auto; border-width:3px;border-style:solid;border-color:black;">
                <tr>
                  <td><label for="departement">Choix du département</label></td>
                  <td><select id="departement" style="width: 100%;">
                          <option value="none" selected="selected"></option>
                          <option value="75">Paris (75)</option>
                          <option value="77">Seine-et-Marne (77)</option>
                          <option value="78">Yvelines (78)</option>
                          <option value="91">Essonne (91)</option>
                          <option value="92">Hauts-de-Seine (92)</option>
                          <option value="93">Seine-Saint-Denis (93)</option>
                          <option value="94">Val-de-Marne (94)</option>
                          <option value="95">Val-d'Oise (95)</option>
                      </select></td>
                </tr>
                <tr>
                    <td><label for="commune">Choix commune</label></td>
                    <td><select id="commune" style="width: 100%;">
                            <option value="none" selected="selected"></option>
                        </select></td>
                </tr>
                <tr>
                    <td><label for="type">Choix du type de patrimoine</label></td>
                    <td><select id="type" style="width: 100%;">
                            <option value="none" selected="selected"></option>
                            <option value="Pont">Pont</option>
                            <option value="Passerelle">Passerelle</option>
                            <option value="Front">Front</option>
                            <option value="Tête">Tête</option>
                            <option value="Ancien">Ancien Pont</option>
                            <option value="Viaduc">Viaduc</option>
                        </select></td>
                </tr>
            </table>
            <div style="text-align:center;margin-top: 10px;">
                <input type="button" onclick="filtrate()" value="Appliquer les filtres" class="btn btn-dark"/>
            </div>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-md-12">
            <table id="tablePatrimoines" class="table table-bordered table-hover table-sm" style="width:95%">
                <thead class="thead-dark">
                <tr>
                    <th>Commune</th>
                    <th>Identifiant</th>
                    <th>Patrimoine</th>
                    <th>Principal</th>
                    <th>Département</th>
                </tr>
                </thead>
                <tbody class="table-striped">

                </tbody>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript" src="assets/jquery.js"></script>
<script type="text/javascript" src="assets/bootstrap.js"></script>
<script type="text/javascript" src="http://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    function filtrate() {
        let communeVal = $("#commune :selected").val();
        let typeVal = $("#type :selected").val();
        let dptVal = $("#departement :selected").val();

        let url = "http://localhost/mission2/cortana.php?search";

        if (communeVal !== 'none') {
            url += "&commune="+communeVal;
        }
        if (typeVal !== 'none') {
            url += "&type="+typeVal;
        }
        if (dptVal !== 'none') {
            url += "&departement="+dptVal;
        }

        let datatable = $('#tablePatrimoines').DataTable();
        $.get(url, function(data) {
            let json = JSON.parse(data);
            datatable.clear();
            datatable.rows.add(json);
            datatable.draw();
        });
    }

    $(document).ready(function () {
        $.get("http://localhost/mission2/cortana.php?search", function (data) {
            let json = JSON.parse(data);
            json.forEach((line) => {
                let option = document.createElement("option");
                option.value = line.commune;
                let commune = document.createTextNode(line.commune);
                option.appendChild(commune);
                document.getElementById('commune').appendChild(option);
            });
            let optionCommuneValues = [];
            $('#commune option').each(function () {
                if ($.inArray(this.value, optionCommuneValues) > -1) {
                    $(this).remove()
                } else {
                    optionCommuneValues.push(this.value);
                }
            });
            $('#tablePatrimoines').DataTable({
                data: json,
                columns: [
                    {title: "Commune"},
                    {title: "Identifiant"},
                    {title: "Patrimoine"},
                    {title: "Principal"},
                    {title: "Département"}
                ]
            });
        });
    });
</script>
</body>
</html>
