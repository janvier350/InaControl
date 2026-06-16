<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
$conexion = conectarse();
$conexion->set_charset("utf8");

if (!isset($_SESSION["rol"])) {
    header("Location: break.php");
    exit();
}
if (!isset($_SESSION['expire']) || time() > $_SESSION['expire']) {
    session_destroy();
    header("Location: expirada.php");
    exit();
}

// ── Parámetros del período ──────────────────────────────────────────────────
$fecha_inicio = $_GET['fecha_inicio'] ?? '2025-05-01';
$fecha_fin    = $_GET['fecha_fin']    ?? '2026-05-31';

// ── 1. Totales generales ────────────────────────────────────────────────────
$sql_total = "
    SELECT
        COUNT(*) AS total_soportes,
        COUNT(DISTINCT A.ID_USUARIO) AS total_tecnicos,
        COUNT(DISTINCT DATE(A.FECHA_SOPORTE)) AS dias_con_soporte,
        ROUND(SUM(TIMESTAMPDIFF(MINUTE, A.HORA_INICIO, A.HORA_FIN)) / 60, 2) AS total_horas
    FROM COTI_CALENDARIO A
    WHERE A.ESTADO_SOPORTE = 'Confirmada'
      AND A.FECHA_SOPORTE BETWEEN ? AND ?";
$st = $conexion->prepare($sql_total);
$st->bind_param("ss", $fecha_inicio, $fecha_fin);
$st->execute();
$totales = $st->get_result()->fetch_assoc();

// ── 2. Soportes por tipo ────────────────────────────────────────────────────
$sql_tipo = "
    SELECT C.SOPORTE AS tipo,
           COUNT(*) AS cantidad,
           ROUND(SUM(TIMESTAMPDIFF(MINUTE, A.HORA_INICIO, A.HORA_FIN)) / 60, 2) AS horas
    FROM COTI_CALENDARIO A
    LEFT JOIN COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
    WHERE A.ESTADO_SOPORTE = 'Confirmada'
      AND A.FECHA_SOPORTE BETWEEN ? AND ?
    GROUP BY C.SOPORTE
    ORDER BY cantidad DESC";
$st2 = $conexion->prepare($sql_tipo);
$st2->bind_param("ss", $fecha_inicio, $fecha_fin);
$st2->execute();
$result_tipo = $st2->get_result();

// ── 3. Soportes por técnico ─────────────────────────────────────────────────
$sql_tec = "
    SELECT CONCAT(U.NOMBRES, ' ', U.APELLIDOS) AS tecnico,
           COUNT(*) AS cantidad,
           ROUND(SUM(TIMESTAMPDIFF(MINUTE, A.HORA_INICIO, A.HORA_FIN)) / 60, 2) AS horas
    FROM COTI_CALENDARIO A
    LEFT JOIN ADM_USUARIO U ON A.ID_USUARIO = U.IDADM_USUARIO
    WHERE A.ESTADO_SOPORTE = 'Confirmada'
      AND A.FECHA_SOPORTE BETWEEN ? AND ?
    GROUP BY A.ID_USUARIO
    ORDER BY cantidad DESC";
$st3 = $conexion->prepare($sql_tec);
$st3->bind_param("ss", $fecha_inicio, $fecha_fin);
$st3->execute();
$result_tec = $st3->get_result();

// ── 4. Evolución mensual ────────────────────────────────────────────────────
$sql_mes = "
    SELECT DATE_FORMAT(A.FECHA_SOPORTE,'%Y-%m') AS periodo,
           DATE_FORMAT(A.FECHA_SOPORTE,'%M %Y') AS etiqueta,
           COUNT(*) AS cantidad,
           ROUND(SUM(TIMESTAMPDIFF(MINUTE, A.HORA_INICIO, A.HORA_FIN)) / 60, 2) AS horas
    FROM COTI_CALENDARIO A
    WHERE A.ESTADO_SOPORTE = 'Confirmada'
      AND A.FECHA_SOPORTE BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(A.FECHA_SOPORTE,'%Y-%m')
    ORDER BY periodo ASC";
$st4 = $conexion->prepare($sql_mes);
$st4->bind_param("ss", $fecha_inicio, $fecha_fin);
$st4->execute();
$result_mes = $st4->get_result();
$meses_data = [];
while ($r = $result_mes->fetch_assoc()) $meses_data[] = $r;

// ── 5. Mes más activo ───────────────────────────────────────────────────────
$mes_pico = null;
if (!empty($meses_data)) {
    $mes_pico = $meses_data[0];
    foreach ($meses_data as $_m) {
        if ($_m['cantidad'] > $mes_pico['cantidad']) $mes_pico = $_m;
    }
}

// ── 6. Promedio mensual ─────────────────────────────────────────────────────
$prom_sop  = count($meses_data) ? round($totales['total_soportes'] / count($meses_data), 1) : 0;
$prom_hrs  = count($meses_data) ? round($totales['total_horas']    / count($meses_data), 1) : 0;


// Helpers
function fmt_hrs($h) {
    $e = floor($h); $m = round(($h - $e) * 60);
    return "$e hrs $m min";
}
function pct($v, $total) {
    return $total ? round($v / $total * 100, 1) : 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Ejecutivo de Soporte Técnico</title>
    <style>
        /* ── Reset & base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a2e; background: #f5f6fa; }

        /* ── Print ── */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .page-break { page-break-before: always; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }

        /* ── Layout ── */
        .container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }

        /* ── Cover ── */
        .cover {
            background: linear-gradient(135deg, #16213e 0%, #0f3460 60%, #1a6491 100%);
            color: #fff;
            padding: 60px 50px 50px;
            border-radius: 12px;
            margin-bottom: 36px;
            position: relative;
            overflow: hidden;
        }
        .cover::after {
            content: '';
            position: absolute;
            right: -60px; top: -60px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .cover .badge-conf {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: #e0e0e0;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 20px;
            margin-bottom: 18px;
        }
        .cover h1 { font-size: 32px; font-weight: 700; line-height: 1.2; margin-bottom: 8px; }
        .cover h2 { font-size: 18px; font-weight: 400; opacity: 0.75; margin-bottom: 30px; }
        .cover .meta { display: flex; gap: 40px; flex-wrap: wrap; margin-top: 30px; }
        .cover .meta-item label { display: block; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; opacity: 0.6; }
        .cover .meta-item span { font-size: 15px; font-weight: 600; }
        .cover .logo { position: absolute; right: 50px; top: 50px; font-size: 40px; opacity: 0.18; font-weight: 900; letter-spacing: -2px; }

        /* ── Section title ── */
        .section-title {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase; color: #0f3460;
            margin: 36px 0 16px;
        }
        .section-title::after {
            content: ''; flex: 1;
            height: 2px; background: linear-gradient(90deg, #0f3460, transparent);
        }

        /* ── KPI cards ── */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 10px; }
        .kpi {
            background: #fff;
            border-radius: 10px;
            padding: 22px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-left: 5px solid #0f3460;
            position: relative;
        }
        .kpi.accent1 { border-color: #1a6491; }
        .kpi.accent2 { border-color: #27ae60; }
        .kpi.accent3 { border-color: #e67e22; }
        .kpi .kpi-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 6px; }
        .kpi .kpi-value { font-size: 34px; font-weight: 700; color: #0f3460; line-height: 1; }
        .kpi .kpi-sub { font-size: 12px; color: #aaa; margin-top: 4px; }

        /* ── Cards ── */
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 22px;
            overflow: hidden;
        }
        .card-header {
            background: #0f3460;
            color: #fff;
            padding: 14px 22px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .card-body { padding: 20px 22px; }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f0f4f8; color: #0f3460; font-weight: 700; padding: 10px 14px; text-align: left; border-bottom: 2px solid #dde4ee; }
        td { padding: 9px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8faff; }
        .badge {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 11px; font-weight: 600;
        }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-orange { background: #ffedd5; color: #9a3412; }

        /* ── Bar chart ── */
        .bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .bar-row .bar-label { min-width: 110px; font-size: 12px; color: #555; text-align: right; }
        .bar-wrap { flex: 1; background: #f0f0f0; border-radius: 4px; height: 22px; overflow: hidden; }
        .bar-fill { height: 100%; background: linear-gradient(90deg, #0f3460, #1a6491); border-radius: 4px; transition: width 0.4s; display: flex; align-items: center; padding-left: 8px; }
        .bar-fill span { font-size: 11px; color: #fff; font-weight: 600; white-space: nowrap; }
        .bar-row .bar-val { min-width: 60px; font-size: 12px; font-weight: 600; color: #0f3460; }

        /* ── Highlights ── */
        .highlights { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        @media (max-width: 650px) { .highlights { grid-template-columns: 1fr; } }
        .highlight-box {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .highlight-box h4 { font-size: 13px; color: #0f3460; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .highlight-box ul { list-style: none; }
        .highlight-box ul li { font-size: 13px; color: #444; padding: 5px 0; border-bottom: 1px solid #f5f5f5; display: flex; gap: 8px; }
        .highlight-box ul li::before { content: '✓'; color: #27ae60; font-weight: 700; flex-shrink: 0; }
        .highlight-box ul li.warn::before { content: '▲'; color: #e67e22; }

        /* ── Comments ── */
        .comment-item {
            border-left: 3px solid #0f3460;
            padding: 8px 12px;
            margin-bottom: 10px;
            background: #f8faff;
            border-radius: 0 6px 6px 0;
            font-size: 12px;
        }
        .comment-item .c-meta { color: #999; font-size: 11px; margin-bottom: 3px; }
        .comment-item .c-text { color: #333; line-height: 1.5; }

        /* ── Footer ── */
        .report-footer {
            text-align: center;
            color: #bbb;
            font-size: 11px;
            margin-top: 40px;
            padding: 20px 0;
            border-top: 1px solid #eee;
        }

        /* ── Buttons ── */
        .btn-print {
            display: inline-flex; align-items: center; gap: 8px;
            background: #0f3460; color: #fff;
            border: none; padding: 10px 22px;
            border-radius: 6px; cursor: pointer;
            font-size: 13px; font-weight: 600;
            text-decoration: none;
        }
        .btn-print:hover { background: #1a6491; }
        .filter-bar {
            background: #fff;
            border-radius: 10px;
            padding: 16px 22px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 24px;
            display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;
        }
        .filter-bar label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #555; display: block; margin-bottom: 4px; }
        .filter-bar input[type=date] {
            padding: 8px 12px; border: 1px solid #dde4ee; border-radius: 6px;
            font-size: 13px; color: #333;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Filtro de fechas -->
    <div class="filter-bar no-print">
        <form method="get" style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
            <div>
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
            </div>
            <div>
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
            </div>
            <button type="submit" class="btn-print" style="background:#27ae60;">&#128269; Actualizar</button>
        </form>
        <div style="margin-left:auto;">
            <button onclick="window.print()" class="btn-print">&#128438; Imprimir / PDF</button>
        </div>
    </div>

    <!-- ══ PORTADA ══════════════════════════════════════════════════════════ -->
    <div class="cover">
        <div class="logo">INAS</div>
        <div class="badge-conf">Confidencial &mdash; Uso Interno</div>
        <h1>Informe Ejecutivo<br>de Soporte Técnico</h1>
        <h2>Resumen de gestión y desempeño operacional</h2>
        <div class="meta">
            <div class="meta-item">
                <label>Período cubierto</label>
                <span><?= date('d M Y', strtotime($fecha_inicio)) ?> &nbsp;—&nbsp; <?= date('d M Y', strtotime($fecha_fin)) ?></span>
            </div>
            <div class="meta-item">
                <label>Fecha de emisión</label>
                <span><?= date('d M Y') ?></span>
            </div>
            <div class="meta-item">
                <label>Elaborado por</label>
                <span>Dpto. de Soporte Técnico &mdash; INASAR</span>
            </div>
        </div>
    </div>

    <!-- ══ KPI RESUMEN ════════════════════════════════════════════════════== -->
    <div class="section-title">&#128200; Indicadores Clave del Período</div>
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi-label">Soportes realizados</div>
            <div class="kpi-value"><?= number_format($totales['total_soportes']) ?></div>
            <div class="kpi-sub">total de atenciones confirmadas</div>
        </div>
        <div class="kpi accent1">
            <div class="kpi-label">Horas de soporte</div>
            <div class="kpi-value"><?= floor($totales['total_horas']) ?><span style="font-size:18px;">h</span></div>
            <div class="kpi-sub"><?= fmt_hrs($totales['total_horas']) ?> en total</div>
        </div>
        <div class="kpi accent2">
            <div class="kpi-label">Promedio mensual</div>
            <div class="kpi-value"><?= $prom_sop ?></div>
            <div class="kpi-sub"><?= $prom_hrs ?> hrs/mes promedio</div>
        </div>
        <div class="kpi accent3">
            <div class="kpi-label">Técnicos activos</div>
            <div class="kpi-value"><?= $totales['total_tecnicos'] ?></div>
            <div class="kpi-sub"><?= $totales['dias_con_soporte'] ?> días con actividad</div>
        </div>
    </div>

    <!-- ══ DISTRIBUCIÓN POR TIPO ══════════════════════════════════════════== -->
    <div class="section-title">&#128203; Distribución por Tipo de Soporte</div>
    <div class="card">
        <div class="card-header">Categorías de soporte &mdash; Cantidad y tiempo invertido</div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo de soporte</th>
                        <th>Cantidad</th>
                        <th>% del total</th>
                        <th>Horas</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; while ($r = $result_tipo->fetch_assoc()): ?>
                    <tr>
                        <td style="color:#aaa;font-size:12px;"><?= $i++ ?></td>
                        <td><strong><?= htmlspecialchars($r['tipo'] ?? 'Sin categoría') ?></strong></td>
                        <td><span class="badge badge-blue"><?= $r['cantidad'] ?></span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:80px;background:#eee;border-radius:3px;height:8px;">
                                    <div style="width:<?= pct($r['cantidad'],$totales['total_soportes']) ?>%;background:#0f3460;height:8px;border-radius:3px;"></div>
                                </div>
                                <?= pct($r['cantidad'],$totales['total_soportes']) ?>%
                            </div>
                        </td>
                        <td><?= fmt_hrs($r['horas']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ DESEMPEÑO POR TÉCNICO ══════════════════════════════════════════== -->
    <div class="section-title">&#128100; Desempeño por Técnico</div>
    <div class="card">
        <div class="card-header">Participación individual &mdash; Soportes y horas</div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>Técnico</th>
                        <th>Soportes</th>
                        <th>Participación</th>
                        <th>Horas trabajadas</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($r = $result_tec->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['tecnico'] ?? 'Sin asignar') ?></strong></td>
                        <td><span class="badge badge-green"><?= $r['cantidad'] ?></span></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:100px;background:#eee;border-radius:3px;height:8px;">
                                    <div style="width:<?= pct($r['cantidad'],$totales['total_soportes']) ?>%;background:#27ae60;height:8px;border-radius:3px;"></div>
                                </div>
                                <?= pct($r['cantidad'],$totales['total_soportes']) ?>%
                            </div>
                        </td>
                        <td><?= fmt_hrs($r['horas']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ EVOLUCIÓN MENSUAL ══════════════════════════════════════════════== -->
    <div class="section-title">&#128197; Evolución Mensual de Actividad</div>
    <div class="card">
        <div class="card-header">Actividad mensual &mdash; Soportes realizados por mes</div>
        <div class="card-body">
            <?php
            $cantidades = array_column($meses_data, 'cantidad');
            $max_sop = !empty($cantidades) ? max($cantidades) : 1;
            if ($max_sop == 0) $max_sop = 1;
            foreach ($meses_data as $r):
                $pct_bar = round($r['cantidad'] / $max_sop * 100);
            ?>
            <div class="bar-row">
                <div class="bar-label"><?= htmlspecialchars($r['etiqueta']) ?></div>
                <div class="bar-wrap">
                    <div class="bar-fill" style="width:<?= max($pct_bar,4) ?>%">
                        <span><?= $r['cantidad'] ?></span>
                    </div>
                </div>
                <div class="bar-val"><?= fmt_hrs($r['horas']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ══ LOGROS Y HALLAZGOS ══════════════════════════════════════════════ -->
    <div class="section-title">&#127942; Logros, Aciertos y Áreas de Dominio</div>
    <div class="highlights">
        <div class="highlight-box">
            <h4>&#9989; Objetivos Alcanzados</h4>
            <ul>
                <li>Cobertura continua de soporte durante los 13 meses del período.</li>
                <li>Atención de <strong><?= $totales['total_soportes'] ?> solicitudes</strong> con estado Confirmada.</li>
                <li>Equipo técnico de <strong><?= $totales['total_tecnicos'] ?></strong> técnico<?= $totales['total_tecnicos'] != 1 ? 's' : '' ?> activo<?= $totales['total_tecnicos'] != 1 ? 's' : '' ?> en el período.</li>
                <?php if ($mes_pico): ?>
                <li>Mes de mayor demanda atendida: <strong><?= htmlspecialchars($mes_pico['etiqueta']) ?></strong> con <?= $mes_pico['cantidad'] ?> soportes.</li>
                <?php endif; ?>
                <li>Promedio de respuesta sostenido: <strong><?= $prom_sop ?> soportes/mes</strong>.</li>
            </ul>
        </div>
        <div class="highlight-box">
            <h4>&#128736; Áreas de Mayor Dominio</h4>
            <ul>
                <?php
                // Reutilizar datos de tipo (ya consumidos, generar resumen estático de los top 3)
                $sql_top3 = "
                    SELECT C.SOPORTE AS tipo, COUNT(*) AS cant
                    FROM COTI_CALENDARIO A
                    LEFT JOIN COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
                    WHERE A.ESTADO_SOPORTE = 'Confirmada'
                      AND A.FECHA_SOPORTE BETWEEN ? AND ?
                    GROUP BY C.SOPORTE ORDER BY cant DESC LIMIT 3";
                $st6 = $conexion->prepare($sql_top3);
                $st6->bind_param("ss", $fecha_inicio, $fecha_fin);
                $st6->execute();
                $top3 = $st6->get_result();
                while ($r = $top3->fetch_assoc()):
                ?>
                <li><?= htmlspecialchars($r['tipo'] ?? 'General') ?> &mdash; <strong><?= $r['cant'] ?> atenciones</strong></li>
                <?php endwhile; ?>
                <li>Gestión documental y registro sistemático de cada soporte.</li>
                <li>Coordinación efectiva de citas y agendamiento técnico.</li>
            </ul>
        </div>
        <div class="highlight-box">
            <h4>&#128200; Métricas de Productividad</h4>
            <ul>
                <li>Total de horas de soporte: <strong><?= fmt_hrs($totales['total_horas']) ?></strong></li>
                <li>Promedio de horas mensuales: <strong><?= fmt_hrs($prom_hrs) ?></strong></li>
                <li>Días con actividad registrada: <strong><?= $totales['dias_con_soporte'] ?></strong></li>
                <li>Meses con actividad sostenida: <strong><?= count($meses_data) ?></strong></li>
            </ul>
        </div>
        <div class="highlight-box">
            <h4>&#127919; Recomendaciones</h4>
            <ul>
                <li>Mantener el registro sistemático en el sistema InaControl para trazabilidad futura.</li>
                <li>Evaluar refuerzo de personal en meses de alta demanda.</li>
                <li>Establecer KPIs de tiempo de resolución por tipo de soporte.</li>
                <li>Revisión trimestral de este informe para seguimiento de tendencias.</li>
            </ul>
        </div>
    </div>


    <!-- ══ FIRMA Y FOOTER ═════════════════════════════════════════════════== -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;margin-top:60px;page-break-inside:avoid;">
        <div style="text-align:center;">
            <div style="height:100px;border-bottom:2px solid #0f3460;margin-bottom:12px;"></div>
            <div style="color:#0f3460;font-weight:600;font-size:13px;">Jefe de Soporte Técnico</div>
            <div style="color:#555;font-size:11px;margin-top:4px;">Ing. Santiago Javier Varas Herrera</div>
            <div style="color:#555;font-size:11px;">OVERCLOCKING &mdash; RUC: 0923006589001</div>
        </div>
        <div style="text-align:center;">
            <div style="height:100px;border-bottom:2px solid #0f3460;margin-bottom:12px;"></div>
            <div style="color:#0f3460;font-weight:600;font-size:13px;">Gerencia General</div>
            <div style="color:#555;font-size:11px;margin-top:4px;">María Belén Sarmiento Orellana</div>
            <div style="color:#555;font-size:11px;">INASAR &mdash; RUC: 0992584246001</div>
        </div>
    </div>

    <div class="report-footer">
        Informe generado automáticamente por el sistema InaControl &mdash; <?= date('d/m/Y H:i') ?> &mdash; Confidencial, uso interno
    </div>

</div>
</body>
</html>
<?php ob_end_flush(); ?>
