
            <!-- Content -->
                            
                <style>
                .dashboard-menu {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 32px;
                    justify-content: center;
                    margin-top: 60px;
                }
                .dashboard-card {
                    background: #fff;
                    border-radius: 16px;
                    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
                    width: 240px;
                    height: 160px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                    font-size: 1.2rem;
                    font-weight: 500;
                    color: #333;
                    cursor: pointer;
                    transition: box-shadow 0.2s, transform 0.2s;
                    border: 2px solid #f0f0f0;
                }
                .dashboard-card:hover {
                    box-shadow: 0 4px 24px rgba(0,0,0,0.16);
                    transform: translateY(-4px) scale(1.03);
                    border-color: #007bff;
                    color: #007bff;
                }
                .dashboard-card i {
                    font-size: 2.5rem;
                    margin-bottom: 18px;
                    color: #007bff;
                }
                </style>

                <div class="dashboard-menu">
                    <a href="<?= base_url('Account/logout') ?>" class="dashboard-card">
                        <i class="ri-logout-line"></i>
                        Logout
                    </a>
                </div>
