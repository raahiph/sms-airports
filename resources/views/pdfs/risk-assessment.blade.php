<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .section { margin: 15px 0; }
        .section-title { background: #f0f0f0; padding: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Risk Assessment Report</h1>
        <p>Generated: {{ $date }}</p>
    </div>

    <div class="section">
        <table>
            <tr>
                <th width="30%">Location</th>
                <td>{{ $hazard['hazard_location'] }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $hazard['hazard_description'] }}</td>
            </tr>
            <tr>
                <th>Risk Rating</th>
                <td>{{ $hazard['risk_rating'] }} ({{ ucfirst($hazard['risk_level']) }})</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Executive Summary</div>
        <div>{{ $assessment['executive_summary'] }}</div>
    </div>

    <div class="section">
        <div class="section-title">Risk Analysis</div>
        <div>{{ $assessment['risk_analysis'] }}</div>
    </div>

    <div class="section">
        <div class="section-title">Impact Assessment</div>
        <div>{{ $assessment['impact_assessment'] }}</div>
    </div>

    <div class="section">
        <div class="section-title">Mitigation Measures</div>
        <div>{{ $assessment['mitigation_measures'] }}</div>
    </div>

    <div class="section">
        <div class="section-title">Implementation Timeline</div>
        <div>{{ $assessment['implementation_timeline'] }}</div>
    </div>

    <div class="section">
        <div class="section-title">Monitoring Requirements</div>
        <div>{{ $assessment['monitoring_requirements'] }}</div>
    </div>

    <div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px;">
        <p><strong>Generated By:</strong> {{ $assessment['generated_by'] }}</p>
        <p><strong>Status:</strong> {{ ucfirst($assessment['status']) }}</p>
        <p><strong>Generated At:</strong> {{ $assessment['generated_at'] }}</p>
    </div>
</body>
</html>