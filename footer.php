
<div class="footer">
  <p>Sistem Koperasi KADA developed by TechniCrab @ 2024/2025</p>
</div>

</body>
</html>
<style>
/* 移除固定定位 */
footer {
    position: relative; /* 改为相对定位 */
    width: 100%;
    background-color: #1a4971;
    color: white;
    padding: 20px 0;
    margin-top: 50px; /* 添加上边距 */
}

/* 确保主内容区域有足够的最小高度 */
.container {
    min-height: calc(100vh - 300px); /* 减去header和footer的高度 */
    padding-bottom: 50px; /* 添加底部内边距 */
}

/* 如果需要，可以添加一个包装器来处理内容 */
.wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.content {
    flex: 1;
}