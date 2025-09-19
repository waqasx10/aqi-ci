import time
from selenium import webdriver
from selenium.webdriver.common.by import By

def test_messages_button():
    driver = webdriver.Chrome()
    driver.maximize_window()
    driver.get("http://127.0.0.1:8000/") 

    message_btn = driver.find_element(By.XPATH, "//button[normalize-space()='Messages']")
    assert message_btn.is_displayed()
    message_btn.click()
    time.sleep(3)  # give time for content to load
    assert "messages" in driver.page_source.lower()
    time.sleep(4)
