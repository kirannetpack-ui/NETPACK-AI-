$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
$project = Split-Path $PSScriptRoot -Parent
foreach ($size in @(48, 72, 96, 144, 180, 192, 512, 1024)) {
    $bitmap = [System.Drawing.Bitmap]::new($size, $size, [System.Drawing.Imaging.PixelFormat]::Format24bppRgb)
    $canvas = [System.Drawing.Graphics]::FromImage($bitmap)
    $canvas.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $canvas.Clear([System.Drawing.ColorTranslator]::FromHtml('#142b3a'))
    $ink = [System.Drawing.ColorTranslator]::FromHtml('#f4f5f3')
    $accent = [System.Drawing.ColorTranslator]::FromHtml('#cf956a')
    $pen = New-Object System.Drawing.Pen($ink, [single]($size * 0.065))
    $pen.StartCap = [System.Drawing.Drawing2D.LineCap]::Round
    $pen.EndCap = [System.Drawing.Drawing2D.LineCap]::Round
    # A simple monogram remains legible and inside the maskable icon safe area.
    $canvas.DrawLines($pen, [System.Drawing.PointF[]]@(
        [System.Drawing.PointF]::new($size*.28,$size*.65),
        [System.Drawing.PointF]::new($size*.28,$size*.35),
        [System.Drawing.PointF]::new($size*.52,$size*.65),
        [System.Drawing.PointF]::new($size*.52,$size*.35)
    ))
    $pen.Color = $accent
    $canvas.DrawLines($pen, [System.Drawing.PointF[]]@(
        [System.Drawing.PointF]::new($size*.65,$size*.40),
        [System.Drawing.PointF]::new($size*.75,$size*.50),
        [System.Drawing.PointF]::new($size*.65,$size*.60)
    ))
    if ($size -in @(180, 192, 512)) {
        $bitmap.Save((Join-Path $project "public/images/app-icon-$size.png"), [System.Drawing.Imaging.ImageFormat]::Png)
    }
    $density = @{48='mdpi';72='hdpi';96='xhdpi';144='xxhdpi';192='xxxhdpi'}[$size]
    if ($density -and (Test-Path (Join-Path $project 'android/app/src/main/res'))) {
        foreach ($name in @('ic_launcher','ic_launcher_round','ic_launcher_foreground')) {
            $bitmap.Save((Join-Path $project "android/app/src/main/res/mipmap-$density/$name.png"), [System.Drawing.Imaging.ImageFormat]::Png)
        }
    }
    if ($size -eq 1024 -and (Test-Path (Join-Path $project 'ios/App/App/Assets.xcassets'))) {
        $bitmap.Save((Join-Path $project 'ios/App/App/Assets.xcassets/AppIcon.appiconset/AppIcon-512@2x.png'), [System.Drawing.Imaging.ImageFormat]::Png)
    }
    $pen.Dispose()
    $canvas.Dispose()
    $bitmap.Dispose()
}
